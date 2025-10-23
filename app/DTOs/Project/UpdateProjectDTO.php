<?php

namespace App\DTOs\Project;

use App\DTOs\Interfaces\DTOInterface;
use App\DTOs\Traits\DTOTrait;

class UpdateProjectDTO implements DTOInterface
{
    use DTOTrait;

    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?string $status = null,
        public ?string $priority = null,
        public ?string $start_date = null,
        public ?string $end_date = null,
        public ?float $budget = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new static(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            status: $data['status'] ?? null,
            priority: $data['priority'] ?? null,
            start_date: $data['start_date'] ?? null,
            end_date: $data['end_date'] ?? null,
            budget: $data['budget'] ?? null,
        );
    }

    public function toUpdateArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'budget' => $this->budget,
        ], fn($value) => $value !== null);
    }

    public function hasUpdates(): bool
    {
        return !empty($this->toUpdateArray());
    }
}
