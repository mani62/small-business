<?php

namespace App\DTOs\Project;

use App\DTOs\Interfaces\DTOInterface;
use App\DTOs\Traits\DTOTrait;

class CreateProjectDTO implements DTOInterface
{
    use DTOTrait;

    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?string $status = 'planning',
        public ?string $priority = 'medium',
        public ?string $start_date = null,
        public ?string $end_date = null,
        public ?float $budget = null,
        public ?int $user_id = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new static(
            name: $data['name'],
            description: $data['description'] ?? null,
            status: $data['status'] ?? 'planning',
            priority: $data['priority'] ?? 'medium',
            start_date: $data['start_date'] ?? null,
            end_date: $data['end_date'] ?? null,
            budget: $data['budget'] ?? null,
            user_id: $data['user_id'] ?? null,
        );
    }

    public function toDatabaseArray(): array
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
