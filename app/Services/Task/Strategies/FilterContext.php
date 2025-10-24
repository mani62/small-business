<?php

namespace App\Services\Task\Strategies;

use Illuminate\Database\Eloquent\Builder;

class FilterContext
{
    private array $strategies = [];

    public function __construct()
    {
        $this->strategies = [
            'status' => new StatusFilterStrategy(),
            'due_date' => new DueDateFilterStrategy(),
            'search' => new SearchFilterStrategy(),
            'overdue' => new OverdueFilterStrategy(),
        ];
    }

    public function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $filterType => $value) {
            if (isset($this->strategies[$filterType]) && $value !== null && $value !== '') {
                $query = $this->strategies[$filterType]->apply($query, $value);
            }
        }

        return $query;
    }

    public function addStrategy(string $filterType, FilterStrategyInterface $strategy): void
    {
        $this->strategies[$filterType] = $strategy;
    }
}
