<?php

namespace App\Services\Task\Strategies;

use Illuminate\Database\Eloquent\Builder;

interface FilterStrategyInterface
{
    public function apply(Builder $query, mixed $value): Builder;
}
