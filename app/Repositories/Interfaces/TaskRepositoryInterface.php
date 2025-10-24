<?php

namespace App\Repositories\Interfaces;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    public function findByStatus(int $userId, string $status): Collection;
    public function findOverdue(int $userId): Collection;
    public function search(int $userId, string $searchTerm): Collection;
    public function create(array $data): Task;
    public function update(Task $task, array $data): Task;
    public function delete(Task $task): bool;
    public function bulkUpdateStatus(array $taskIds, string $status): int;
    public function getStatistics(int $userId): array;
}
