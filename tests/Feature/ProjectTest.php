<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    Sanctum::actingAs($this->user);
});

describe('Project CRUD Operations', function () {
    
    describe('GET /api/v1/projects', function () {
        it('can retrieve user projects', function () {
            Project::factory()->count(3)->create(['user_id' => $this->user->id]);
            Project::factory()->count(2)->create(['user_id' => $this->otherUser->id]);

            $response = $this->getJson('/api/v1/projects');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'name',
                                'description',
                                'status',
                                'priority',
                                'start_date',
                                'end_date',
                                'budget',
                                'user_id',
                                'created_at',
                                'updated_at',
                                'progress_percentage',
                                'is_overdue'
                            ]
                        ]
                    ],
                    'meta' => [
                        'total',
                        'per_page',
                        'current_page',
                        'last_page'
                    ]
                ]);

            expect($response->json('data.data'))->toHaveCount(3);
        });

        it('can filter projects by status', function () {
            Project::factory()->create(['user_id' => $this->user->id, 'status' => 'completed']);
            Project::factory()->create(['user_id' => $this->user->id, 'status' => 'in_progress']);

            $response = $this->getJson('/api/v1/projects?status=completed');

            $response->assertStatus(200);
            expect($response->json('data.data'))->toHaveCount(1);
            expect($response->json('data.data.0.status'))->toBe('completed');
        });

        it('can filter projects by priority', function () {
            Project::factory()->create(['user_id' => $this->user->id, 'priority' => 'high']);
            Project::factory()->create(['user_id' => $this->user->id, 'priority' => 'low']);

            $response = $this->getJson('/api/v1/projects?priority=high');

            $response->assertStatus(200);
            expect($response->json('data.data'))->toHaveCount(1);
            expect($response->json('data.data.0.priority'))->toBe('high');
        });

        it('can search projects by name and description', function () {
            Project::factory()->create([
                'user_id' => $this->user->id,
                'name' => 'Laravel Project',
                'description' => 'A web application'
            ]);
            Project::factory()->create([
                'user_id' => $this->user->id,
                'name' => 'React App',
                'description' => 'Frontend application'
            ]);

            $response = $this->getJson('/api/v1/projects?search=Laravel');

            $response->assertStatus(200);
            expect($response->json('data.data'))->toHaveCount(1);
            expect($response->json('data.data.0.name'))->toBe('Laravel Project');
        });

        it('can sort projects by different fields', function () {
            $project1 = Project::factory()->create([
                'user_id' => $this->user->id,
                'name' => 'Alpha Project',
                'created_at' => now()->subDays(2)
            ]);
            $project2 = Project::factory()->create([
                'user_id' => $this->user->id,
                'name' => 'Beta Project',
                'created_at' => now()->subDays(1)
            ]);

            $response = $this->getJson('/api/v1/projects?sort_by=name&sort_order=asc');

            $response->assertStatus(200);
            expect($response->json('data.data.0.name'))->toBe('Alpha Project');
            expect($response->json('data.data.1.name'))->toBe('Beta Project');
        });

    });

    describe('POST /api/v1/projects', function () {
        it('can create a new project', function () {
            $projectData = [
                'name' => 'Test Project',
                'description' => 'A test project description',
                'status' => 'planning',
                'priority' => 'high',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'budget' => 10000.50
            ];

            $response = $this->postJson('/api/v1/projects', $projectData);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'description',
                        'status',
                        'priority',
                        'start_date',
                        'end_date',
                        'budget',
                        'user_id',
                        'created_at',
                        'updated_at',
                        'progress_percentage',
                        'is_overdue'
                    ]
                ]);

            expect($response->json('data.name'))->toBe('Test Project');
            expect($response->json('data.user_id'))->toBe($this->user->id);

            $this->assertDatabaseHas('projects', [
                'name' => 'Test Project',
                'user_id' => $this->user->id
            ]);
        });

        it('can create a project with minimal data', function () {
            $projectData = [
                'name' => 'Minimal Project'
            ];

            $response = $this->postJson('/api/v1/projects', $projectData);

            $response->assertStatus(201);
            expect($response->json('data.name'))->toBe('Minimal Project');
            expect($response->json('data.status'))->toBe('planning');
            expect($response->json('data.priority'))->toBe('medium');
        });

        it('validates required fields', function () {
            $response = $this->postJson('/api/v1/projects', []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
        });

        it('validates field constraints', function () {
            $projectData = [
                'name' => 'A', // Too short
                'description' => str_repeat('a', 2001), // Too long
                'status' => 'invalid_status',
                'priority' => 'invalid_priority',
                'budget' => -100, // Negative
                'start_date' => '2024-12-31',
                'end_date' => '2024-01-01' // Before start date
            ];

            $response = $this->postJson('/api/v1/projects', $projectData);

            $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'name',
                    'description',
                    'status',
                    'priority',
                    'budget',
                    'end_date'
                ]);
        });

    });

    describe('GET /api/v1/projects/{project}', function () {
        it('can retrieve a specific project', function () {
            $project = Project::factory()->create(['user_id' => $this->user->id]);

            $response = $this->getJson("/api/v1/projects/{$project->id}");

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'description',
                        'status',
                        'priority',
                        'start_date',
                        'end_date',
                        'budget',
                        'user_id',
                        'created_at',
                        'updated_at',
                        'progress_percentage',
                        'is_overdue'
                    ]
                ]);

            expect($response->json('data.id'))->toBe($project->id);
        });

        it('cannot retrieve another user project', function () {
            $project = Project::factory()->create(['user_id' => $this->otherUser->id]);

            $response = $this->getJson("/api/v1/projects/{$project->id}");

            $response->assertStatus(404);
        });

        it('returns 404 for non-existent project', function () {
            $response = $this->getJson('/api/v1/projects/999');

            $response->assertStatus(404);
        });

    });

    describe('PUT/PATCH /api/v1/projects/{project}', function () {
        it('can update a project', function () {
            $project = Project::factory()->create(['user_id' => $this->user->id]);

            $updateData = [
                'name' => 'Updated Project Name',
                'status' => 'in_progress',
                'priority' => 'urgent'
            ];

            $response = $this->putJson("/api/v1/projects/{$project->id}", $updateData);

            $response->assertStatus(200);
            expect($response->json('data.name'))->toBe('Updated Project Name');
            expect($response->json('data.status'))->toBe('in_progress');
            expect($response->json('data.priority'))->toBe('urgent');

            $this->assertDatabaseHas('projects', [
                'id' => $project->id,
                'name' => 'Updated Project Name',
                'status' => 'in_progress',
                'priority' => 'urgent'
            ]);
        });

        it('can partially update a project with PATCH', function () {
            $project = Project::factory()->create([
                'user_id' => $this->user->id,
                'name' => 'Original Name',
                'status' => 'planning'
            ]);

            $response = $this->patchJson("/api/v1/projects/{$project->id}", [
                'name' => 'Updated Name Only'
            ]);

            $response->assertStatus(200);
            expect($response->json('data.name'))->toBe('Updated Name Only');
            expect($response->json('data.status'))->toBe('planning'); // Should remain unchanged
        });

        it('cannot update another user project', function () {
            $project = Project::factory()->create(['user_id' => $this->otherUser->id]);

            $response = $this->putJson("/api/v1/projects/{$project->id}", [
                'name' => 'Hacked Project'
            ]);

            $response->assertStatus(404);
        });

        it('validates update data', function () {
            $project = Project::factory()->create(['user_id' => $this->user->id]);

            $response = $this->putJson("/api/v1/projects/{$project->id}", [
                'name' => '', // Empty name
                'status' => 'invalid_status'
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'status']);
        });

    });

    describe('DELETE /api/v1/projects/{project}', function () {
        it('can soft delete a project', function () {
            $project = Project::factory()->create(['user_id' => $this->user->id]);

            $response = $this->deleteJson("/api/v1/projects/{$project->id}");

            $response->assertStatus(200)
                ->assertJson(['message' => 'Project deleted successfully']);

            $this->assertSoftDeleted('projects', ['id' => $project->id]);
        });

        it('cannot delete another user project', function () {
            $project = Project::factory()->create(['user_id' => $this->otherUser->id]);

            $response = $this->deleteJson("/api/v1/projects/{$project->id}");

            $response->assertStatus(404);
        });

    });

    describe('POST /api/v1/projects/{id}/restore', function () {
        it('can restore a soft deleted project', function () {
            $project = Project::factory()->create(['user_id' => $this->user->id]);
            $project->delete();

            $response = $this->postJson("/api/v1/projects/{$project->id}/restore");

            $response->assertStatus(200)
                ->assertJson(['message' => 'Project restored successfully']);

            $this->assertDatabaseHas('projects', [
                'id' => $project->id,
                'deleted_at' => null
            ]);
        });

        it('cannot restore another user project', function () {
            $project = Project::factory()->create(['user_id' => $this->otherUser->id]);
            $project->delete();

            $response = $this->postJson("/api/v1/projects/{$project->id}/restore");

            $response->assertStatus(404);
        });
    });

    describe('DELETE /api/v1/projects/{id}/force-delete', function () {
        it('can permanently delete a project', function () {
            $project = Project::factory()->create(['user_id' => $this->user->id]);

            $response = $this->deleteJson("/api/v1/projects/{$project->id}/force-delete");

            $response->assertStatus(200)
                ->assertJson(['message' => 'Project permanently deleted']);

            $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        });

        it('cannot permanently delete another user project', function () {
            $project = Project::factory()->create(['user_id' => $this->otherUser->id]);

            $response = $this->deleteJson("/api/v1/projects/{$project->id}/force-delete");

            $response->assertStatus(404);
        });
    });

    describe('GET /api/v1/projects/statistics', function () {
        it('can retrieve project statistics', function () {
            Project::factory()->create([
                'user_id' => $this->user->id,
                'status' => 'completed',
                'priority' => 'high',
                'budget' => 1000
            ]);
            Project::factory()->create([
                'user_id' => $this->user->id,
                'status' => 'in_progress',
                'priority' => 'medium',
                'budget' => 2000
            ]);
            Project::factory()->create([
                'user_id' => $this->user->id,
                'status' => 'completed',
                'priority' => 'low',
                'budget' => 500
            ]);

            $response = $this->getJson('/api/v1/projects/statistics');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'total_projects',
                        'completed_projects',
                        'in_progress_projects',
                        'overdue_projects',
                        'total_budget',
                        'status_distribution',
                        'priority_distribution'
                    ]
                ]);

            expect($response->json('data.total_projects'))->toBe(3);
            expect($response->json('data.completed_projects'))->toBe(2);
            expect($response->json('data.in_progress_projects'))->toBe(1);
            expect($response->json('data.total_budget'))->toBe('3500.00');
        });

    });
});

describe('Project Model Relationships and Methods', function () {
    it('belongs to a user', function () {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        expect($project->user)->toBeInstanceOf(User::class);
        expect($project->user->id)->toBe($user->id);
    });

    it('has many projects', function () {
        $user = User::factory()->create();
        Project::factory()->count(3)->create(['user_id' => $user->id]);

        expect($user->projects)->toHaveCount(3);
    });

    it('can calculate progress percentage', function () {
        $project = Project::factory()->create(['status' => 'completed']);
        expect($project->getProgressPercentage())->toBe(100);

        $project = Project::factory()->create(['status' => 'in_progress']);
        expect($project->getProgressPercentage())->toBe(50);

        $project = Project::factory()->create(['status' => 'planning']);
        expect($project->getProgressPercentage())->toBe(10);
    });

    it('can detect overdue projects', function () {
        $overdueProject = Project::factory()->create([
            'end_date' => now()->subDays(1),
            'status' => 'in_progress'
        ]);
        expect($overdueProject->isOverdue())->toBeTrue();

        $completedProject = Project::factory()->create([
            'end_date' => now()->subDays(1),
            'status' => 'completed'
        ]);
        expect($completedProject->isOverdue())->toBeFalse();

        $futureProject = Project::factory()->create([
            'end_date' => now()->addDays(1),
            'status' => 'in_progress'
        ]);
        expect($futureProject->isOverdue())->toBeFalse();
    });

    it('can scope projects by user', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        Project::factory()->count(2)->create(['user_id' => $user1->id]);
        Project::factory()->count(3)->create(['user_id' => $user2->id]);

        $user1Projects = Project::forUser($user1->id)->get();
        expect($user1Projects)->toHaveCount(2);

        $user2Projects = Project::forUser($user2->id)->get();
        expect($user2Projects)->toHaveCount(3);
    });

    it('can scope projects by status', function () {
        Project::factory()->create(['status' => 'completed']);
        Project::factory()->create(['status' => 'in_progress']);
        Project::factory()->create(['status' => 'completed']);

        $completedProjects = Project::byStatus('completed')->get();
        expect($completedProjects)->toHaveCount(2);
    });

    it('can scope projects by priority', function () {
        Project::factory()->create(['priority' => 'high']);
        Project::factory()->create(['priority' => 'medium']);
        Project::factory()->create(['priority' => 'high']);

        $highPriorityProjects = Project::byPriority('high')->get();
        expect($highPriorityProjects)->toHaveCount(2);
    });
});
