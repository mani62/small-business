<?php

namespace App\DTOs\Task;

use App\DTOs\Interfaces\DTOInterface;
use App\DTOs\Traits\DTOTrait;

class UpdateTaskDTO implements DTOInterface
{
    use DTOTrait;

    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?string $status = null,
        public ?string $due_date = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new static(
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            status: $data['status'] ?? null,
            due_date: $data['due_date'] ?? null,
        );
    }

    public function hasUpdates(): bool
    {
        return $this->title !== null || 
               $this->description !== null || 
               $this->status !== null || 
               $this->due_date !== null;
    }

    public function toUpdateArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->due_date,
        ], fn($value) => $value !== null);
    }

    public function toApiArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->due_date,
        ];
    }
}
