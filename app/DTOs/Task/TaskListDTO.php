<?php

namespace App\DTOs\Task;

use App\DTOs\Interfaces\DTOInterface;
use App\DTOs\Traits\DTOTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskListDTO implements DTOInterface
{
    use DTOTrait;

    public function __construct(
        public array $data = [],
        public ?int $current_page = null,
        public ?int $last_page = null,
        public ?int $per_page = null,
        public ?int $total = null,
        public ?string $first_page_url = null,
        public ?string $last_page_url = null,
        public ?string $next_page_url = null,
        public ?string $prev_page_url = null,
    ) {}

    public static function fromPaginator(LengthAwarePaginator $paginator): static
    {
        return new static(
            data: $paginator->items(),
            current_page: $paginator->currentPage(),
            last_page: $paginator->lastPage(),
            per_page: $paginator->perPage(),
            total: $paginator->total(),
            first_page_url: $paginator->url(1),
            last_page_url: $paginator->url($paginator->lastPage()),
            next_page_url: $paginator->nextPageUrl(),
            prev_page_url: $paginator->previousPageUrl(),
        );
    }

    public function toApiArray(): array
    {
        return [
            'data' => $this->data,
            'pagination' => [
                'current_page' => $this->current_page,
                'last_page' => $this->last_page,
                'per_page' => $this->per_page,
                'total' => $this->total,
                'first_page_url' => $this->first_page_url,
                'last_page_url' => $this->last_page_url,
                'next_page_url' => $this->next_page_url,
                'prev_page_url' => $this->prev_page_url,
            ],
        ];
    }
}
