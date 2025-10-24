<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\CreateTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Task;
use App\Services\Task\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {}

    /**
     * Display a listing of the user's tasks.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $tasks = $this->taskService->getTasks($user, $request);

            return response()->json([
                'success' => true,
                'data' => $tasks,
                'message' => 'Tasks retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Store a newly created task.
     */
    public function store(CreateTaskRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $validatedData = $request->validated();
            
            $task = $this->taskService->createTask($user, $validatedData, $request);

            return response()->json([
                'success' => true,
                'data' => $task,
                'message' => 'Task created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Display the specified task.
     */
    public function show(Request $request, Task $task): JsonResponse
    {
        try {
            $user = $request->user();
            
            if ($task->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found'
                ], 404);
            }
            
            $taskData = $this->taskService->getTask($user, $task, $request);

            return response()->json([
                'success' => true,
                'data' => $taskData,
                'message' => 'Task retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        try {
            $user = $request->user();
            
            if ($task->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found'
                ], 404);
            }
            
            $validatedData = $request->validated();
            $taskData = $this->taskService->updateTask($user, $task, $validatedData, $request);

            return response()->json([
                'success' => true,
                'data' => $taskData,
                'message' => 'Task updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task, Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if ($task->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found'
                ], 404);
            }
            
            $this->taskService->deleteTask($user, $task, $request);

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Get task statistics for the authenticated user.
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $stats = $this->taskService->getTaskStatistics($user, $request);

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Task statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Search tasks by title or description.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $searchTerm = $request->query('q');
            
            if (!$searchTerm) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search term is required'
                ], 400);
            }

            $tasks = $this->taskService->searchTasks($user, $searchTerm, $request);

            return response()->json([
                'success' => true,
                'data' => $tasks,
                'message' => 'Search completed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Get tasks by status.
     */
    public function byStatus(string $status, Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $tasks = $this->taskService->getTasksByStatus($user, $status, $request);

            return response()->json([
                'success' => true,
                'data' => $tasks,
                'message' => "Tasks with status '{$status}' retrieved successfully"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Get overdue tasks.
     */
    public function overdue(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $tasks = $this->taskService->getOverdueTasks($user, $request);

            return response()->json([
                'success' => true,
                'data' => $tasks,
                'message' => 'Overdue tasks retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Bulk update task status.
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $request->validate([
                'task_ids' => 'required|array',
                'task_ids.*' => 'integer|exists:tasks,id',
                'status' => 'required|string|in:todo,in_progress,done'
            ]);

            $taskIds = $request->input('task_ids');
            $status = $request->input('status');

            $result = $this->taskService->bulkUpdateStatus($user, $taskIds, $status, $request);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => $result['message']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}
