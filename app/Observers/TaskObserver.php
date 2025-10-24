<?php

namespace App\Observers;

use App\Models\Task;
use App\Services\Task\Enums\TaskStatus;
use Illuminate\Support\Facades\Log;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        Log::info('Task created', [
            'task_id' => $task->id,
            'title' => $task->title,
            'status' => $task->status,
            'user_id' => $task->user_id,
        ]);
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        Log::info('Task updated', [
            'task_id' => $task->id,
            'title' => $task->title,
            'status' => $task->status,
            'user_id' => $task->user_id,
            'changes' => $task->getChanges(),
        ]);

        if ($task->wasChanged('status') && $task->status === TaskStatus::DONE->value) {
            Log::info('Task completed', [
                'task_id' => $task->id,
                'title' => $task->title,
                'user_id' => $task->user_id,
            ]);
        }

        if ($task->wasChanged('due_date') && $task->isOverdue()) {
            Log::warning('Task became overdue', [
                'task_id' => $task->id,
                'title' => $task->title,
                'due_date' => $task->due_date,
                'user_id' => $task->user_id,
            ]);
        }
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        Log::info('Task deleted', [
            'task_id' => $task->id,
            'title' => $task->title,
            'status' => $task->status,
            'user_id' => $task->user_id,
        ]);
    }
}
