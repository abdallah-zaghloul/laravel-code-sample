<?php

namespace App\Data;

use App\Enums\HTTPCodeEnum;
use App\Models\User;
use App\Utils\Response;
use Arr;
use Spatie\LaravelData\Data;
use Illuminate\Validation\Validator;

abstract class BaseData extends Data
{
    use Response;
    public static function withValidator(Validator $validator): void
    {
        if ($validator->fails()) {
            static::error(
                code: HTTPCodeEnum::UNPROCESSABLE_ENTITY,
                errors: $validator->errors(),
            );
        }
    }

    public function onlyFilled()
    {
        return array_filter($this->toArray(), fn($value) => filled($value));
    }

    public static function user(string|null $guard = null): ?User
    {
        return request()->user($guard);
    }
}
