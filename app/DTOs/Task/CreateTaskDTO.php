<?php

namespace App\DTOs\Task;

use App\DTOs\Interfaces\DTOInterface;
use App\DTOs\Traits\DTOTrait;

class CreateTaskDTO implements DTOInterface
{
    use DTOTrait;

    public function __construct(
        public string $title,
        public ?string $description = null,
        public string $status = 'todo',
        public ?string $due_date = null,
        public ?int $user_id = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new static(
            title: $data['title'],
            description: $data['description'] ?? null,
            status: $data['status'] ?? 'todo',
            due_date: $data['due_date'] ?? null,
            user_id: $data['user_id'] ?? null,
        );
    }

    public function toDatabaseArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->due_date,
            'user_id' => $this->user_id,
        ], fn($value) => $value !== null);
    }

    public function toApiArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->due_date,
            'user_id' => $this->user_id,
        ];
    }
}
