<?php

namespace App\Services\Task\Strategies;

use App\Services\Task\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;

class StatusFilterStrategy implements FilterStrategyInterface
{
    public function apply(Builder $query, mixed $value): Builder
    {
        if (in_array($value, TaskStatus::getValues())) {
            return $query->where('status', $value);
        }
        
        return $query;
    }
}
