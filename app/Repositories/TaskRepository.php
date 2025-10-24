<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Services\Task\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function findByStatus(int $userId, string $status): Collection
    {
        return Task::forUser($userId)->byStatus($status)->get();
    }

    public function findOverdue(int $userId): Collection
    {
        return Task::forUser($userId)->overdue()->get();
    }

    public function search(int $userId, string $searchTerm): Collection
    {
        return Task::forUser($userId)->search($searchTerm)->get();
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task->fresh();
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    public function bulkUpdateStatus(array $taskIds, string $status): int
    {
        return Task::whereIn('id', $taskIds)->update(['status' => $status]);
    }

    public function getStatistics(int $userId): array
    {
        $baseQuery = Task::forUser($userId);
        
        return [
            'total_tasks' => $baseQuery->count(),
            'todo_tasks' => (clone $baseQuery)->byStatus(TaskStatus::TODO->value)->count(),
            'in_progress_tasks' => (clone $baseQuery)->byStatus(TaskStatus::IN_PROGRESS->value)->count(),
            'done_tasks' => (clone $baseQuery)->byStatus(TaskStatus::DONE->value)->count(),
            'overdue_tasks' => (clone $baseQuery)->overdue()->count(),
            'status_distribution' => (clone $baseQuery)->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
        ];
    }
}
