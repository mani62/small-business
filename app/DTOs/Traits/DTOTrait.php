<?php

namespace App\DTOs\Traits;

trait DTOTrait
{
    public static function fromArray(array $data): static
    {
        $dto = new static();
        
        foreach ($data as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->$key = $value;
            }
        }
        
        return $dto;
    }

    public function toArray(): array
    {
        $array = [];
        
        foreach (get_object_vars($this) as $key => $value) {
            if ($value !== null) {
                $array[$key] = $value;
            }
        }
        
        return $array;
    }

    public function toArrayWithNulls(): array
    {
        return get_object_vars($this);
    }

    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->$key ?? $default;
    }

    public function set(string $key, mixed $value): static
    {
        if (property_exists($this, $key)) {
            $this->$key = $value;
        }
        
        return $this;
    }

    public function has(string $key): bool
    {
        return property_exists($this, $key) && $this->$key !== null;
    }
}

