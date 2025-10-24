<?php

use App\Models\Task;
use App\Models\User;
use App\Services\Task\Enums\TaskStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'sanctum');
});

test('user can create a task', function () {
    $taskData = [
        'title' => 'Test Task',
        'description' => 'This is a test task',
        'status' => 'todo',
        'due_date' => '2024-12-31',
    ];

    $response = $this->postJson('/api/v1/tasks', $taskData);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Task created successfully',
        ])
        ->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'status',
                'due_date',
                'user_id',
                'created_at',
                'updated_at',
                'progress_percentage',
                'is_overdue',
            ],
        ]);

    $this->assertDatabaseHas('tasks', [
        'title' => 'Test Task',
        'description' => 'This is a test task',
        'status' => 'todo',
        'user_id' => $this->user->id,
    ]);
});

test('user can retrieve their tasks', function () {
    Task::factory()->count(3)->create(['user_id' => $this->user->id]);

    $response = $this->getJson('/api/v1/tasks');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Tasks retrieved successfully',
        ])
        ->assertJsonStructure([
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'status',
                        'due_date',
                        'user_id',
                        'created_at',
                        'updated_at',
                        'progress_percentage',
                        'is_overdue',
                    ],
                ],
                'pagination',
            ],
        ]);

    expect($response->json('data.data'))->toHaveCount(3);
});

test('user can retrieve a specific task', function () {
    $task = Task::factory()->create(['user_id' => $this->user->id]);

    $response = $this->getJson("/api/v1/tasks/{$task->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Task retrieved successfully',
        ])
        ->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'status',
                'due_date',
                'user_id',
                'created_at',
                'updated_at',
                'progress_percentage',
                'is_overdue',
            ],
        ]);
});

test('user can update a task', function () {
    $task = Task::factory()->create(['user_id' => $this->user->id]);

    $updateData = [
        'title' => 'Updated Task Title',
        'status' => 'in_progress',
    ];

    $response = $this->putJson("/api/v1/tasks/{$task->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Task updated successfully',
        ]);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated Task Title',
        'status' => 'in_progress',
    ]);
});

test('user can delete a task', function () {
    $task = Task::factory()->create(['user_id' => $this->user->id]);

    $response = $this->deleteJson("/api/v1/tasks/{$task->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Task deleted successfully',
        ]);

    $this->assertDatabaseMissing('tasks', [
        'id' => $task->id,
    ]);
});

test('user cannot access other users tasks', function () {
    $otherUser = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->getJson("/api/v1/tasks/{$task->id}");

    $response->assertStatus(404);
});

test('user can filter tasks by status', function () {
    Task::factory()->todo()->create(['user_id' => $this->user->id]);
    Task::factory()->inProgress()->create(['user_id' => $this->user->id]);
    Task::factory()->done()->create(['user_id' => $this->user->id]);

    $response = $this->getJson('/api/v1/tasks/status/todo');

    $response->assertStatus(200);
    
    $tasks = $response->json('data');
    foreach ($tasks as $task) {
        expect($task['status'])->toBe('todo');
    }
});

test('user can search tasks', function () {
    Task::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Important Meeting',
        'description' => 'Meeting with client',
    ]);
    
    Task::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Code Review',
        'description' => 'Review pull request',
    ]);

    $response = $this->getJson('/api/v1/tasks/search?q=meeting');

    $response->assertStatus(200);
    
    $tasks = $response->json('data');
    expect($tasks)->toHaveCount(1);
    expect($tasks[0]['title'])->toBe('Important Meeting');
});

test('user can get overdue tasks', function () {
    Task::factory()->overdue()->create(['user_id' => $this->user->id]);
    Task::factory()->create([
        'user_id' => $this->user->id,
        'due_date' => now()->addDays(5),
    ]);

    $response = $this->getJson('/api/v1/tasks/overdue');

    $response->assertStatus(200);
    
    $data = $response->json('data');
    expect($data['count'])->toBe(1);
});

test('user can get task statistics', function () {
    Task::factory()->count(2)->create([
        'user_id' => $this->user->id,
        'status' => 'todo',
        'due_date' => now()->addDays(5) // Not overdue
    ]);
    Task::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'status' => 'in_progress'
    ]);
    Task::factory()->count(1)->create([
        'user_id' => $this->user->id,
        'status' => 'done'
    ]);
    Task::factory()->count(2)->create([
        'user_id' => $this->user->id,
        'due_date' => now()->subDays(5),
        'status' => 'todo'
    ]);

    $response = $this->getJson('/api/v1/tasks/statistics');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Task statistics retrieved successfully',
        ])
        ->assertJsonStructure([
            'data' => [
                'total_tasks',
                'todo_tasks',
                'in_progress_tasks',
                'done_tasks',
                'overdue_tasks',
                'status_distribution',
            ],
        ]);

    $stats = $response->json('data');
    
    expect($stats['total_tasks'])->toBe(8);
    expect($stats['todo_tasks'])->toBe(4); // 2 + 2 overdue
    expect($stats['in_progress_tasks'])->toBe(3);
    expect($stats['done_tasks'])->toBe(1);
    expect($stats['overdue_tasks'])->toBe(2);
});

test('user can bulk update task status', function () {
    $tasks = Task::factory()->count(3)->create(['user_id' => $this->user->id]);
    $taskIds = $tasks->pluck('id')->toArray();

    $response = $this->postJson('/api/v1/tasks/bulk-update-status', [
        'task_ids' => $taskIds,
        'status' => 'done',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    foreach ($tasks as $task) {
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'done',
        ]);
    }
});

test('task validation works correctly', function () {
    $response = $this->postJson('/api/v1/tasks', [
        'title' => '', // Empty title should fail
        'status' => 'invalid_status', // Invalid status should fail
        'due_date' => 'invalid_date', // Invalid date should fail
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'status', 'due_date']);
});

test('task pagination works correctly', function () {
    Task::factory()->count(25)->create(['user_id' => $this->user->id]);

    $response = $this->getJson('/api/v1/tasks?per_page=10');

    $response->assertStatus(200);
    
    $data = $response->json('data');
    expect($data['data'])->toHaveCount(10);
    expect($data['pagination']['per_page'])->toBe(10);
    expect($data['pagination']['total'])->toBe(25);
});

test('task sorting works correctly', function () {
    Task::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Alpha Task',
        'created_at' => now()->subDays(2),
    ]);
    
    Task::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Beta Task',
        'created_at' => now()->subDays(1),
    ]);

    $response = $this->getJson('/api/v1/tasks?sort_by=title&sort_order=asc');

    $response->assertStatus(200);
    
    $tasks = $response->json('data.data');
    expect($tasks[0]['title'])->toBe('Alpha Task');
    expect($tasks[1]['title'])->toBe('Beta Task');
});
