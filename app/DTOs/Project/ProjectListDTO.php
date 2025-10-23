<?php

namespace App\DTOs\Project;

use App\DTOs\Interfaces\DTOInterface;
use App\DTOs\Traits\DTOTrait;

class ProjectListDTO implements DTOInterface
{
    use DTOTrait;

    public function __construct(
        public array $projects = [],
        public int $total = 0,
        public int $per_page = 15,
        public int $current_page = 1,
        public int $last_page = 1,
        public ?int $from = null,
        public ?int $to = null,
    ) {}

    public static function fromPaginator(\Illuminate\Pagination\LengthAwarePaginator $paginator): static
    {
        $projects = $paginator->getCollection()->map(function ($project) {
            return ProjectDTO::fromModel($project)->toApiArray();
        })->toArray();

        return new static(
            projects: $projects,
            total: $paginator->total(),
            per_page: $paginator->perPage(),
            current_page: $paginator->currentPage(),
            last_page: $paginator->lastPage(),
            from: $paginator->firstItem(),
            to: $paginator->lastItem(),
        );
    }

    public function toApiArray(): array
    {
        return [
            'data' => $this->projects,
            'meta' => [
                'total' => $this->total,
                'per_page' => $this->per_page,
                'current_page' => $this->current_page,
                'last_page' => $this->last_page,
                'from' => $this->from,
                'to' => $this->to,
            ],
        ];
    }

    public function withMetadata(array $metadata): static
    {
        $this->projects = array_merge($this->projects, $metadata);
        return $this;
    }
}
