<?php

namespace App\DTOs;

use App\DTOs\Interfaces\DTOInterface;
use App\DTOs\Traits\DTOTrait;

class GenericDTO implements DTOInterface
{
    use DTOTrait;

    protected array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    public function toArray(): array
    {
        return array_filter($this->data, fn($value) => $value !== null);
    }

    public function toArrayWithNulls(): array
    {
        return $this->data;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): static
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data) && $this->data[$key] !== null;
    }

    public function remove(string $key): static
    {
        unset($this->data[$key]);
        return $this;
    }

    public function keys(): array
    {
        return array_keys($this->data);
    }

    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function merge(array|DTOInterface $data): static
    {
        $array = $data instanceof DTOInterface ? $data->toArray() : $data;
        $this->data = array_merge($this->data, $array);
        return $this;
    }

    public function only(array $keys): static
    {
        $filtered = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $this->data)) {
                $filtered[$key] = $this->data[$key];
            }
        }
        return new static($filtered);
    }

    public function except(array $keys): static
    {
        $filtered = $this->data;
        foreach ($keys as $key) {
            unset($filtered[$key]);
        }
        return new static($filtered);
    }
}
