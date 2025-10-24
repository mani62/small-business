<?php

namespace App\Services\Task\Strategies;

use Illuminate\Database\Eloquent\Builder;

class SearchFilterStrategy implements FilterStrategyInterface
{
    public function apply(Builder $query, mixed $value): Builder
    {
        if ($value) {
            return $query->where(function ($q) use ($value) {
                $q->where('title', 'like', "%{$value}%")
                  ->orWhere('description', 'like', "%{$value}%");
            });
        }
        
        return $query;
    }
}
