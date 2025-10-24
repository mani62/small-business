<?php

namespace App\DTOs\Task;

use App\DTOs\Interfaces\DTOInterface;
use App\DTOs\Traits\DTOTrait;

class TaskStatisticsDTO implements DTOInterface
{
    use DTOTrait;

    public function __construct(
        public int $total_tasks = 0,
        public int $todo_tasks = 0,
        public int $in_progress_tasks = 0,
        public int $done_tasks = 0,
        public int $overdue_tasks = 0,
        public array $status_distribution = [],
    ) {}

    public static function fromArray(array $stats): static
    {
        return new static(
            total_tasks: $stats['total_tasks'] ?? 0,
            todo_tasks: $stats['todo_tasks'] ?? 0,
            in_progress_tasks: $stats['in_progress_tasks'] ?? 0,
            done_tasks: $stats['done_tasks'] ?? 0,
            overdue_tasks: $stats['overdue_tasks'] ?? 0,
            status_distribution: $stats['status_distribution'] ?? [],
        );
    }

    public function toApiArray(): array
    {
        return [
            'total_tasks' => $this->total_tasks,
            'todo_tasks' => $this->todo_tasks,
            'in_progress_tasks' => $this->in_progress_tasks,
            'done_tasks' => $this->done_tasks,
            'overdue_tasks' => $this->overdue_tasks,
            'status_distribution' => $this->status_distribution,
        ];
    }
}
