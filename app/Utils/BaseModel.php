<?php

namespace App\Utils;

use Illuminate\Contracts\Support\Arrayable;

trait BaseModel
{
    public function arrayOf(iterable $data): array
    {
        return $data instanceof Arrayable
            ? $data->toArray()
            : iterator_to_array($data);
    }

    public function __construct(iterable $attributes = [])
    {
        parent::__construct(static::arrayOf($attributes));
    }

    public function create(iterable $attributes = [])
    {
        return parent::create(static::arrayOf($attributes));
    }

    public function update(
        iterable $attributes = [],
        iterable $options = []
    ) {
        return parent::update(static::arrayOf($attributes), static::arrayOf($options));
    }

    public function fill(iterable $attributes = [])
    {
        return parent::fill(static::arrayOf($attributes));
    }

    public function forceFill(iterable $attributes = [])
    {
        return parent::forceFill(static::arrayOf($attributes));
    }
}
