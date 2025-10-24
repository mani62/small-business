<?php

namespace App\DTOs\Task;

use App\DTOs\Interfaces\DTOInterface;
use App\DTOs\Traits\DTOTrait;

class TaskDTO implements DTOInterface
{
    use DTOTrait;

    public function __construct(
        public ?int $id = null,
        public ?string $title = null,
        public ?string $description = null,
        public ?string $status = null,
        public ?string $due_date = null,
        public ?int $user_id = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?int $progress_percentage = null,
        public ?bool $is_overdue = null,
    ) {}

    public static function fromModel(\App\Models\Task $task): static
    {
        return new static(
            id: $task->id,
            title: $task->title,
            description: $task->description,
            status: $task->status,
            due_date: $task->due_date?->format('Y-m-d'),
            user_id: $task->user_id,
            created_at: $task->created_at?->toISOString(),
            updated_at: $task->updated_at?->toISOString(),
            progress_percentage: $task->progress_percentage ?? $task->getProgressPercentage(),
            is_overdue: $task->is_overdue ?? $task->isOverdue(),
        );
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->due_date,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'progress_percentage' => $this->progress_percentage,
            'is_overdue' => $this->is_overdue,
        ];
    }

    public function toFillableArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->due_date,
            'user_id' => $this->user_id,
        ], fn($value) => $value !== null);
    }
}
