<?php

namespace App\Queries;

use App\Data\OrderSearchData;
use App\Enums\FlagEnum;
use App\Models\Order;
use DB;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * Summary of OrderQuery
 * @property Order $model
 * @property QueryBuilder $query
 * $this->getModel() = $this->model,
 * $this->getQuery() = $this->query = DB::table($this->model->getTable())
 * but it autofill $table & $connection & global scopes from $this->model
 */
class OrderQuery extends BaseQuery
{
    public function search(OrderSearchData $data): static
    {
        return $this->when(
            filled($data?->name),
            fn(self $q) => $q->byName($data->name)
        );
    }


    public function byName(string $name): static
    {
        return $this->where('name', 'like', "%$name%");
    }


    public function byFlags(?iterable $flags): QueryBuilder
    {
        return $this->query->when(
            filled($flags),
            fn($query) => $query->where(
                fn($query) => FlagEnum::whereJson(
                    $query,
                    'flags_json',
                    $flags,
                    'or'
                )
            )
        );
    }

}

