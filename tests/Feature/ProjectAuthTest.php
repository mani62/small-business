<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Project Authentication Tests', function () {
    
    it('requires authentication for GET /api/v1/projects', function () {
        $response = $this->getJson('/api/v1/projects');

        $response->assertStatus(401);
    });

    it('requires authentication for POST /api/v1/projects', function () {
        $projectData = ['name' => 'Test Project'];
        $response = $this->postJson('/api/v1/projects', $projectData);

        $response->assertStatus(401);
    });

    it('requires authentication for GET /api/v1/projects/{project}', function () {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(401);
    });

    it('requires authentication for PUT /api/v1/projects/{project}', function () {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->putJson("/api/v1/projects/{$project->id}", [
            'name' => 'Updated Name'
        ]);

        $response->assertStatus(401);
    });

    it('requires authentication for DELETE /api/v1/projects/{project}', function () {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(401);
    });

    it('requires authentication for GET /api/v1/projects/statistics', function () {
        $response = $this->getJson('/api/v1/projects/statistics');

        $response->assertStatus(401);
    });
});
