<?php

namespace App\Services\Task;

use App\DTOs\Task\CreateTaskDTO;
use App\DTOs\Task\TaskDTO;
use App\DTOs\Task\TaskListDTO;
use App\DTOs\Task\TaskStatisticsDTO;
use App\DTOs\Task\UpdateTaskDTO;
use App\Models\Task;
use App\Models\User;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Services\Task\Enums\TaskStatus;
use App\Services\Task\Strategies\FilterContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskService
{
    private FilterContext $filterContext;

    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {
        $this->filterContext = new FilterContext();
    }

    /**
     * Display a listing of the user's tasks with filtering and pagination.
     */
    public function getTasks(User $user, Request $request): array
    {
        try {
            $query = Task::forUser($user->id);

            $filters = $request->only(['status', 'due_date', 'search', 'overdue']);
            $query = $this->filterContext->applyFilters($query, $filters);

            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $allowedSortFields = ['title', 'status', 'due_date', 'created_at', 'updated_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $perPage = min($request->get('per_page', 15), 100);
            $tasks = $query->paginate($perPage);

            $tasks->getCollection()->transform(function ($task) {
                $task->progress_percentage = $this->getTaskProgressPercentage($task);
                $task->is_overdue = $this->isTaskOverdue($task);
                return $task;
            });

            $taskListDTO = TaskListDTO::fromPaginator($tasks);

            return $taskListDTO->toApiArray();

        } catch (\Exception $e) {
            Log::error('Failed to retrieve tasks', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Store a new task.
     */
    public function createTask(User $user, array $validatedData, Request $request): array
    {
        try {
            DB::beginTransaction();

            $validatedData['user_id'] = $user->id;

            $createTaskDTO = CreateTaskDTO::fromRequest($validatedData);
            $task = $this->taskRepository->create($createTaskDTO->toDatabaseArray());
            $task->load('user');

            $task->progress_percentage = $this->getTaskProgressPercentage($task);
            $task->is_overdue = $this->isTaskOverdue($task);

            $taskDTO = TaskDTO::fromModel($task);

            DB::commit();

            Log::info('Task created successfully', [
                'task_id' => $task->id,
                'user_id' => $user->id,
                'task_title' => $task->title,
                'ip' => $request->ip()
            ]);

            return $taskDTO->toApiArray();

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create task', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Get a task.
     */
    public function getTask(User $user, Task $task, Request $request): array
    {
        try {
            if ($task->user_id !== $user->id) {
                throw new \Exception('Task not found', 404);
            }

            $task->load('user');
            
            $task->progress_percentage = $this->getTaskProgressPercentage($task);
            $task->is_overdue = $this->isTaskOverdue($task);

            $taskDTO = TaskDTO::fromModel($task);

            return $taskDTO->toApiArray();

        } catch (\Exception $e) {
            Log::error('Failed to retrieve task', [
                'error' => $e->getMessage(),
                'task_id' => $task->id ?? null,
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Update a task.
     */
    public function updateTask(User $user, Task $task, array $validatedData, Request $request): array
    {
        try {
            if ($task->user_id !== $user->id) {
                throw new \Exception('Task not found', 404);
            }

            DB::beginTransaction();

            $updateTaskDTO = UpdateTaskDTO::fromRequest($validatedData);
            
            if ($updateTaskDTO->hasUpdates()) {
                $task = $this->taskRepository->update($task, $updateTaskDTO->toUpdateArray());
            }
            
            $task->load('user');

            $task->progress_percentage = $this->getTaskProgressPercentage($task);
            $task->is_overdue = $this->isTaskOverdue($task);

            $taskDTO = TaskDTO::fromModel($task);

            DB::commit();

            Log::info('Task updated successfully', [
                'task_id' => $task->id,
                'user_id' => $user->id,
                'task_title' => $task->title,
                'ip' => $request->ip()
            ]);

            return $taskDTO->toApiArray();

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update task', [
                'error' => $e->getMessage(),
                'task_id' => $task->id ?? null,
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Delete a task.
     */
    public function deleteTask(User $user, Task $task, Request $request): void
    {
        try {
            if ($task->user_id !== $user->id) {
                throw new \Exception('Task not found', 404);
            }

            DB::beginTransaction();

            $this->taskRepository->delete($task);

            DB::commit();

            Log::info('Task deleted successfully', [
                'task_id' => $task->id,
                'user_id' => $user->id,
                'task_title' => $task->title,
                'ip' => $request->ip()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete task', [
                'error' => $e->getMessage(),
                'task_id' => $task->id ?? null,
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Get task statistics for the authenticated user.
     */
    public function getTaskStatistics(User $user, Request $request): array
    {
        try {
            $stats = $this->taskRepository->getStatistics($user->id);
            $statisticsDTO = TaskStatisticsDTO::fromArray($stats);

            return $statisticsDTO->toApiArray();

        } catch (\Exception $e) {
            Log::error('Failed to retrieve task statistics', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Search tasks by title or description.
     */
    public function searchTasks(User $user, string $searchTerm, Request $request): array
    {
        try {
            if (empty($searchTerm)) {
                throw new \Exception('Search term is required', 400);
            }

            $tasks = $this->taskRepository->search($user->id, $searchTerm);

            $tasks->transform(function ($task) {
                $task->progress_percentage = $this->getTaskProgressPercentage($task);
                $task->is_overdue = $this->isTaskOverdue($task);
                return $task;
            });

            $taskDTOs = $tasks->map(fn($task) => TaskDTO::fromModel($task));

            return $taskDTOs->map(fn($dto) => $dto->toApiArray())->toArray();

        } catch (\Exception $e) {
            Log::error('Failed to search tasks', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'search_term' => $searchTerm,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Get tasks by status.
     */
    public function getTasksByStatus(User $user, string $status, Request $request): array
    {
        try {
            if (!in_array($status, TaskStatus::getValues())) {
                throw new \Exception('Invalid status provided', 400);
            }

            $tasks = $this->taskRepository->findByStatus($user->id, $status);

            $tasks->transform(function ($task) {
                $task->progress_percentage = $this->getTaskProgressPercentage($task);
                $task->is_overdue = $this->isTaskOverdue($task);
                return $task;
            });

            $taskDTOs = $tasks->map(fn($task) => TaskDTO::fromModel($task));

            return $taskDTOs->map(fn($dto) => $dto->toApiArray())->toArray();

        } catch (\Exception $e) {
            Log::error('Failed to retrieve tasks by status', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'status' => $status,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Get overdue tasks.
     */
    public function getOverdueTasks(User $user, Request $request): array
    {
        try {
            $overdueTasks = $this->taskRepository->findOverdue($user->id);

            $overdueTasks->transform(function ($task) {
                $task->progress_percentage = $this->getTaskProgressPercentage($task);
                $task->is_overdue = $this->isTaskOverdue($task);
                return $task;
            });

            $taskDTOs = $overdueTasks->map(fn($task) => TaskDTO::fromModel($task));

            return [
                'data' => $taskDTOs->map(fn($dto) => $dto->toApiArray())->toArray(),
                'count' => $overdueTasks->count()
            ];

        } catch (\Exception $e) {
            Log::error('Failed to retrieve overdue tasks', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Bulk update task status.
     */
    public function bulkUpdateStatus(User $user, array $taskIds, string $status, Request $request): array
    {
        try {
            if (!in_array($status, TaskStatus::getValues())) {
                throw new \Exception('Invalid status provided', 400);
            }

            $userTasks = $user->tasks()->whereIn('id', $taskIds)->pluck('id');
            if ($userTasks->count() !== count($taskIds)) {
                throw new \Exception('Some tasks not found or do not belong to you', 404);
            }

            DB::beginTransaction();

            $updatedCount = $this->taskRepository->bulkUpdateStatus($taskIds, $status);

            DB::commit();

            Log::info('Tasks bulk status updated', [
                'user_id' => $user->id,
                'task_ids' => $taskIds,
                'status' => $status,
                'updated_count' => $updatedCount,
                'ip' => $request->ip()
            ]);

            return [
                'updated_count' => $updatedCount,
                'message' => "Successfully updated {$updatedCount} tasks to '{$status}' status"
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to bulk update task status', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Get task progress percentage based on status.
     */
    private function getTaskProgressPercentage(Task $task): int
    {
        $status = TaskStatus::tryFrom($task->status);
        return $status ? $status->getProgressPercentage() : 0;
    }

    /**
     * Check if task is overdue.
     */
    private function isTaskOverdue(Task $task): bool
    {
        return $task->due_date && 
               $task->due_date->isPast() && 
               $task->status !== TaskStatus::DONE->value;
    }
}
