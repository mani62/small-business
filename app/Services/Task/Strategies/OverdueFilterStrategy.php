<?php

namespace App\Services\Task\Strategies;

use Illuminate\Database\Eloquent\Builder;

class OverdueFilterStrategy implements FilterStrategyInterface
{
    public function apply(Builder $query, mixed $value): Builder
    {
        if ($value === true || $value === 'true' || $value === '1') {
            return $query->where('due_date', '<', now())
                        ->where('status', '!=', 'done');
        }
        
        return $query;
    }
}
