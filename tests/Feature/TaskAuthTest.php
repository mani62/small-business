<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('task creation requires authentication', function () {
    $taskData = [
        'title' => 'Test Task',
        'description' => 'This is a test task',
    ];

    $response = $this->postJson('/api/v1/tasks', $taskData);

    $response->assertStatus(401);
});

test('task retrieval requires authentication', function () {
    $response = $this->getJson('/api/v1/tasks');

    $response->assertStatus(401);
});

test('task update requires authentication', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $user->id]);

    $response = $this->putJson("/api/v1/tasks/{$task->id}", [
        'title' => 'Updated Task'
    ]);

    $response->assertStatus(401);
});

test('task deletion requires authentication', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $user->id]);

    $response = $this->deleteJson("/api/v1/tasks/{$task->id}");

    $response->assertStatus(401);
});
