<?php

namespace App\Services\Task\Strategies;

use Illuminate\Database\Eloquent\Builder;

class DueDateFilterStrategy implements FilterStrategyInterface
{
    public function apply(Builder $query, mixed $value): Builder
    {
        if ($value) {
            return $query->where('due_date', $value);
        }
        
        return $query;
    }
}
