<?php
namespace App\Utils;

use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use function Illuminate\Support\enum_value;

trait IterableEnum
{

    /** @return array<string> */
    public static function names(): array
    {
        return array_column(static::cases(), 'name');
    }

    /** @return array<string|int> */
    public static function values(): array
    {
        return array_map(
            fn($case) => enum_value($case),
            static::cases()
        );
    }


    /** @return array{string: string|int} */
    public static function assoc(): array
    {
        return array_combine(static::names(), static::values());
    }


    /** @return Collection<string, string|int> */
    public static function toAssoc(): Collection
    {
        return collect(static::assoc());
    }


    /** @return Collection<int, static> */
    public static function toCases(): Collection
    {
        return collect(static::cases());
    }


    /** @return Collection<int,string> */
    public static function toNames(): Collection
    {
        return collect(static::names());
    }


    /** @return Collection<int,string|int> */
    public static function toValues(): Collection
    {
        return collect(static::values());
    }


    /** @return Collection<int|string, Stringable> */
    public static function toTrans(
        array $replace = [],
        string $trans_file = 'enums',
        string|null $locale = null
    ): Collection {
        return static::toCases()->flatMap(fn(self $case) => [
            $case->value() => $case->trans($replace, $trans_file, $locale)
        ]);
    }


    /** @return string */
    public static function toSet(string|iterable $value = null): string
    {
        return self::quote(static::toCommaSeparated($value));
    }


    public static function toJson(string|iterable|null $value = null): ?string
    {
        return static::toStrings($value)?->values()?->toJson();
    }


    public static function toStrings(string|iterable|null $value = null): ?Collection
    {
        return static::fromAny($value ?? static::cases())?->map?->toString();
    }


    public static function toCommaSeparated(null|string|iterable $value = null): ?string
    {
        return static::toStrings($value)?->implode(',');
    }


    public static function find(mixed $value): ?static
    {
        return match (true) {
            $value instanceof static => $value,
            is_subclass_of(
                static::class,
                BackedEnum::class
            ) => static::tryFrom($value),
            default => static::toCases()->first(
                fn($case) => $case->name == enum_value($value)
            )
        };

    }


    public static function findOrFail(mixed $value): static
    {
        return static::find($value) ?? static::invalidException();
    }


    private static function quote(string|self $case): string
    {
        return DB::getPdo()->quote($case instanceof static ? $case->toString() : $case);
    }


    private static function invalidException(): never
    {
        throw new InvalidArgumentException(
            str('Case must be instance or enum_value() of: ')
                ->append(static::class)
        );
    }


    public function toQuote(): string
    {
        return self::quote($this);
    }


    public function toString(): Stringable
    {
        return str($this->value());
    }


    public function value(): int|string
    {
        return enum_value($this);
    }


    public function isEqual(mixed $value): bool
    {
        return $this === static::find($value);
    }


    public function transKey(): string
    {
        return static::class . '-' . $this->name;
    }


    public function trans(
        $replace = [],
        $trans_file = "enums",
        string|null $locale = null
    ): Stringable {
        return str(trans(
            key: "$trans_file.{$this->transKey()}",
            replace: $replace,
            locale: $locale
        ));
    }


    /** @return Collection<static> */
    public static function fromIterable(iterable $value): Collection
    {
        return collect($value)
            ->map(fn($case) => static::findOrFail($case))
            ->unique();
    }


    /** @return ?Collection<static> */
    public static function fromNullableString(?string $value): ?Collection
    {
        if (isset($value)) {
            $decoded = @json_decode($value);
            return static::fromIterable(match (true) {
                is_null($decoded) => explode(',', $value),
                is_array($decoded) => $decoded,
                default => [$decoded]
            });
        }

        return $value;
    }


    /**
     * @param 'json'|'set' $type
     * @return ?Collection<static>
     */
    public static function fromAny(
        string|iterable|Expression|null $value,
        string $type = 'json',
        ?Model $model = null
    ): ?Collection {
        return match (true) {
            is_iterable($value) => static::fromIterable($value),
            $value instanceof Expression => static::fromExpression($value, $model, $type),
            default => static::fromNullableString($value)
        };
    }

    /**
     * @param 'json'|'set' $type
     * @return ?Collection<static>
     */
    public static function fromExpression(
        Expression $expression,
        Model $model,
        string $type = 'json',
    ): ?Collection {
        return match ($type) {
            "json" => static::fromJsonQuery($expression, $model),
            "set" => static::fromSetQuery($expression, $model),
            default => throw new InvalidArgumentException("Invalid expression type.")
        };
    }


    public static function setUpsert(
        string $column,
        ?iterable $value,
        ?Collection $old = null
    ): Expression {
        if (is_null($value))
            return DB::raw("NULL");

        $is_append = static::popIsAppend($value);
        isset($old) && $is_append && $value = $old->merge($value);
        $cases = static::fromIterable($value);
        $set = static::toSet($cases);
        $upsert = $cases->map(fn($case) => strtr(
            "IF(NOT FIND_IN_SET(enum, COALESCE($column, '')), enum, NULL)",
            ['enum' => $case->toQuote()]
        ))->implode(", ");

        return DB::raw(
            str("CONCAT_WS(',', NULLIF($column, ''), $upsert)")
                ->unless(
                    $is_append,
                    fn($q) => $q->substrReplace($set)
                )
        );
    }


    public static function jsonUpsert(
        string $column,
        ?iterable $value,
        ?Collection $old = null
    ): Expression {
        if (is_null($value))
            return DB::raw("NULL");

        $is_append = static::popIsAppend($value);
        isset($old) && $is_append && $value = $old->merge($value);
        $json_array = static::toJson($value);

        return DB::raw(
            str("(SELECT JSON_ARRAYAGG(value) FROM (SELECT DISTINCT value FROM JSON_TABLE(")
                ->append(
                    "JSON_MERGE_PRESERVE(COALESCE($column, JSON_ARRAY()),'$json_array'),",
                    "'$[*]' COLUMNS (value VARCHAR(191) PATH '$')) AS enums) AS uniques)"
                )->unless(
                    $is_append,
                    fn($q) => $q->substrReplace(
                        "'$json_array'"
                    )
                )
        );
    }


    /** @return ?Collection<static> */
    public static function fromJsonQuery(Expression $expression, Model $model): ?Collection
    {
        $value = static::expToStr($expression, $model)
            ->betweenFirst("'", "'");
        $value->exactly('NULL') && $value = null;
        return static::fromNullableString($value);
    }


    public static function expToStr(Expression $expression, Model $model): Stringable
    {
        return str($expression->getValue(
            DB::connection($model->getConnection()->getName())
                ->getQueryGrammar()
        ));
    }


    /** @return ?Collection<static> */
    public static function fromSetQuery(Expression $expression, Model $model)
    {
        $value = static::expToStr($expression, $model)
            ->when(
                fn($q) => $q->startsWith("'"),
                fn($q) => $q->betweenFirst("'", "'")
            )->when(
                fn($q) => $q->startsWith("CONCAT_WS"),
                fn($q) => $q->substrReplace(
                    $q->matchAll("/'([^',]+)'/")
                        ->unique()
                        ->implode(',')
                )
            );

        $value->exactly("NULL") && $value = null;
        return static::fromNullableString($value);
    }


    public static function popIsAppend(iterable &$value): bool
    {
        throw_unless(
            is_bool(@$value["is_append"] ??= true),
            InvalidArgumentException::class,
            "is_append key must be a boolean."
        );

        $is_append = $value["is_append"];
        unset($value["is_append"]);
        return $is_append;
    }


    /**
     * @param QueryBuilder|EloquentBuilder $query
     * @param 'or'| 'and' $operator
     * @return QueryBuilder|EloquentBuilder
     */
    public static function whereSet(
        $query,
        string $column,
        string|iterable $value,
        string $operator = 'or'
    ) {
        //set() database index start from 1, max values is 62 item
        $bitmask = static::fromAny($value)
            ->map(fn($case) => 1 << static::toCases()->search($case))
            ->implode(' | ');

        return match ($operator) {
            "and" => $query->whereRaw("($column & ($bitmask)) = ($bitmask)"),
            default => $query->whereRaw("($column & ($bitmask)) != 0")
        };
    }

    /**
     * @param QueryBuilder|EloquentBuilder $query
     * @param 'or'| 'and' $operator
     * @return QueryBuilder|EloquentBuilder
     */
    public static function whereMemberOf(
        $query,
        string $column,
        string|iterable $value,
        string $operator = 'or'
    ) {
        static::toStrings($value)->each(fn($enum) => match ($operator) {
            "and" => $query->whereRaw("'$enum' MEMBER OF($column)"),
            default => $query->orWhereRaw("'$enum' MEMBER OF($column)")
        });
        return $query;
    }


    /**
     * @param QueryBuilder|EloquentBuilder $query
     * @param 'or'| 'and' $operator
     * @return QueryBuilder|EloquentBuilder
     */
    public static function whereJson(
        $query,
        string $column,
        string|iterable $value,
        string $operator = 'or'
    ) {
        $search = static::toJson($value);
        return match ($operator) {
            "and" => $query->whereRaw("JSON_CONTAINS($column, '$search')"),
            default => $query->whereRaw("JSON_OVERLAPS($column, '$search')")
        };
    }


    private static function jsonSchema(string $type): string
    {
        return json_encode([
            "type" => "array",
            "items" => [
                "type" => $type,
                "enum" => static::values()
            ],
            "uniqueItems" => true
        ]);
    }


    private static function toQuotes(): string
    {
        return static::toCases()
            ->map(fn($case) => $case->toQuote())
            ->implode(',');
    }


    public static function jsonConstraint(string $table, string $column, string $type = "string"): bool
    {
        $json_schema = static::jsonSchema($type);
        return DB::statement(
            "ALTER TABLE $table ADD CONSTRAINT $column CHECK (
                        $column IS NULL OR
                        JSON_SCHEMA_VALID('$json_schema', $column)
                    );"
        );
    }

    public static function createJsonIndex(string $table, string $column): bool
    {
        return DB::statement("CREATE INDEX $column ON $table ((CAST($column->'$[*]' AS CHAR(191) ARRAY)));");
    }

    public static function enumConstraint(string $table, string $column): bool
    {
        $enum_schema = static::toQuotes();
        return DB::statement(
            "ALTER TABLE $table ADD CONSTRAINT $column CHECK (
                        $column IS NULL OR
                        $column IN ($enum_schema)
                    );"
        );
    }


    public static function dropConstraint(string $table, string $column): bool
    {
        return DB::statement("ALTER TABLE $table DROP CHECK $column");
    }
}
