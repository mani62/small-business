<?php

namespace App\DTOs\User;

use App\DTOs\Interfaces\DTOInterface;
use App\DTOs\Traits\DTOTrait;

class UserDTO implements DTOInterface
{
    use DTOTrait;

    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $email = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?int $projects_count = null,
    ) {}

    /**
     * Create UserDTO from User model.
     *
     * @param \App\Models\User $user
     * @return static
     */
    public static function fromModel(\App\Models\User $user): static
    {
        return new static(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            created_at: $user->created_at?->toISOString(),
            updated_at: $user->updated_at?->toISOString(),
            projects_count: $user->projects_count ?? $user->projects()->count(),
        );
    }

    /**
     * Convert to array for API response.
     *
     * @return array
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'projects_count' => $this->projects_count,
        ];
    }

    /**
     * Get only the fillable fields for database operations.
     *
     * @return array
     */
    public function toFillableArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
        ], fn($value) => $value !== null);
    }
}
