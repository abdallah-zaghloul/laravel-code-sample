<?php

namespace App\Queries;

use App\Data\OrderSearchData;
use App\Enums\FlagEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * Summary of BaseQuery
 * @property Model $model
 * @property QueryBuilder $query
 * $this->getModel() = $this->model,
 * $this->getQuery() = $this->query = DB::table($this->model->getTable())
 * but it autofill $table & $connection & global scopes from $this->model
 */
class BaseQuery extends EloquentBuilder
{


}
