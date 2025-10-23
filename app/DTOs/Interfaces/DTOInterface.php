<?php

namespace App\DTOs\Interfaces;

interface DTOInterface
{
    public static function fromArray(array $data): static;

    public function toArray(): array;

    public function toArrayWithNulls(): array;

    public function toJson(int $options = 0): string;

    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value): static;

    public function has(string $key): bool;
}
