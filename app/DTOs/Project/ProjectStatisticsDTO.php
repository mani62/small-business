<?php

namespace App\DTOs\Project;

use App\DTOs\Interfaces\DTOInterface;
use App\DTOs\Traits\DTOTrait;

class ProjectStatisticsDTO implements DTOInterface
{
    use DTOTrait;

    public function __construct(
        public int $total_projects = 0,
        public int $completed_projects = 0,
        public int $in_progress_projects = 0,
        public int $overdue_projects = 0,
        public float $total_budget = 0.0,
        public array $status_distribution = [],
        public array $priority_distribution = [],
    ) {}

    public static function fromArray(array $stats): static
    {
        return new static(
            total_projects: $stats['total_projects'] ?? 0,
            completed_projects: $stats['completed_projects'] ?? 0,
            in_progress_projects: $stats['in_progress_projects'] ?? 0,
            overdue_projects: $stats['overdue_projects'] ?? 0,
            total_budget: $stats['total_budget'] ?? 0.0,
            status_distribution: $stats['status_distribution'] ?? [],
            priority_distribution: $stats['priority_distribution'] ?? [],
        );
    }

    public function getCompletionPercentage(): float
    {
        if ($this->total_projects === 0) {
            return 0.0;
        }
        
        return round(($this->completed_projects / $this->total_projects) * 100, 2);
    }

    public function getOverduePercentage(): float
    {
        if ($this->total_projects === 0) {
            return 0.0;
        }
        
        return round(($this->overdue_projects / $this->total_projects) * 100, 2);
    }

    public function toApiArray(): array
    {
        return [
            'total_projects' => $this->total_projects,
            'completed_projects' => $this->completed_projects,
            'in_progress_projects' => $this->in_progress_projects,
            'overdue_projects' => $this->overdue_projects,
            'total_budget' => number_format($this->total_budget, 2, '.', ''),
            'completion_percentage' => $this->getCompletionPercentage(),
            'overdue_percentage' => $this->getOverduePercentage(),
            'status_distribution' => $this->status_distribution,
            'priority_distribution' => $this->priority_distribution,
        ];
    }
}
