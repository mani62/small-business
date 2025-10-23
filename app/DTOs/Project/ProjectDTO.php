<?php

namespace App\DTOs\Project;

use App\DTOs\Interfaces\DTOInterface;
use App\DTOs\Traits\DTOTrait;

class ProjectDTO implements DTOInterface
{
    use DTOTrait;

    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $status = null,
        public ?string $priority = null,
        public ?string $start_date = null,
        public ?string $end_date = null,
        public ?float $budget = null,
        public ?int $user_id = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?string $deleted_at = null,
        public ?int $progress_percentage = null,
        public ?bool $is_overdue = null,
    ) {}

    public static function fromModel(\App\Models\Project $project): static
    {
        return new static(
            id: $project->id,
            name: $project->name,
            description: $project->description,
            status: $project->status,
            priority: $project->priority,
            start_date: $project->start_date?->format('Y-m-d'),
            end_date: $project->end_date?->format('Y-m-d'),
            budget: $project->budget,
            user_id: $project->user_id,
            created_at: $project->created_at?->toISOString(),
            updated_at: $project->updated_at?->toISOString(),
            deleted_at: $project->deleted_at?->toISOString(),
            progress_percentage: $project->progress_percentage ?? $project->getProgressPercentage(),
            is_overdue: $project->is_overdue ?? $project->isOverdue(),
        );
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'budget' => $this->budget,
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
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'budget' => $this->budget,
            'user_id' => $this->user_id,
        ], fn($value) => $value !== null);
    }
}
