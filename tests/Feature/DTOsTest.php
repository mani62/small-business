<?php

use App\DTOs\Project\CreateProjectDTO;
use App\DTOs\Project\ProjectDTO;
use App\DTOs\Project\ProjectStatisticsDTO;
use App\DTOs\Project\UpdateProjectDTO;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('DTO Functionality Tests', function () {
    
    it('can create CreateProjectDTO from array', function () {
        $data = [
            'name' => 'Test Project',
            'description' => 'Test Description',
            'status' => 'planning',
            'priority' => 'high',
            'budget' => 1000.50
        ];

        $dto = CreateProjectDTO::fromArray($data);

        expect($dto->name)->toBe('Test Project');
        expect($dto->description)->toBe('Test Description');
        expect($dto->status)->toBe('planning');
        expect($dto->priority)->toBe('high');
        expect($dto->budget)->toBe(1000.50);
    });

    it('can convert CreateProjectDTO to array', function () {
        $dto = new CreateProjectDTO(
            name: 'Test Project',
            description: 'Test Description',
            status: 'planning',
            priority: 'high',
            budget: 1000.50
        );

        $array = $dto->toArray();

        expect($array)->toHaveKey('name');
        expect($array)->toHaveKey('description');
        expect($array)->toHaveKey('status');
        expect($array)->toHaveKey('priority');
        expect($array)->toHaveKey('budget');
        expect($array['name'])->toBe('Test Project');
    });

    it('can create ProjectDTO from model', function () {
        $user = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Project',
            'status' => 'completed'
        ]);

        $dto = ProjectDTO::fromModel($project);

        expect($dto->id)->toBe($project->id);
        expect($dto->name)->toBe('Test Project');
        expect($dto->status)->toBe('completed');
        expect($dto->user_id)->toBe($user->id);
    });

    it('can convert ProjectDTO to API array', function () {
        $user = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Project',
            'status' => 'completed'
        ]);

        $dto = ProjectDTO::fromModel($project);
        $apiArray = $dto->toApiArray();

        expect($apiArray)->toHaveKey('id');
        expect($apiArray)->toHaveKey('name');
        expect($apiArray)->toHaveKey('status');
        expect($apiArray)->toHaveKey('progress_percentage');
        expect($apiArray)->toHaveKey('is_overdue');
        expect($apiArray['name'])->toBe('Test Project');
    });

    it('can create UpdateProjectDTO with partial data', function () {
        $data = [
            'name' => 'Updated Name',
            'status' => 'in_progress'
        ];

        $dto = UpdateProjectDTO::fromRequest($data);

        expect($dto->name)->toBe('Updated Name');
        expect($dto->status)->toBe('in_progress');
        expect($dto->description)->toBeNull();
        expect($dto->priority)->toBeNull();
    });

    it('can check if UpdateProjectDTO has updates', function () {
        $dtoWithUpdates = UpdateProjectDTO::fromRequest(['name' => 'Updated']);
        $dtoWithoutUpdates = UpdateProjectDTO::fromRequest([]);

        expect($dtoWithUpdates->hasUpdates())->toBeTrue();
        expect($dtoWithoutUpdates->hasUpdates())->toBeFalse();
    });

    it('can get only update fields from UpdateProjectDTO', function () {
        $data = [
            'name' => 'Updated Name',
            'status' => 'in_progress',
            'description' => null // This should be excluded
        ];

        $dto = UpdateProjectDTO::fromRequest($data);
        $updateArray = $dto->toUpdateArray();

        expect($updateArray)->toHaveKey('name');
        expect($updateArray)->toHaveKey('status');
        expect($updateArray)->not->toHaveKey('description');
        expect($updateArray)->not->toHaveKey('priority');
    });

    it('can create ProjectStatisticsDTO from array', function () {
        $stats = [
            'total_projects' => 10,
            'completed_projects' => 5,
            'in_progress_projects' => 3,
            'overdue_projects' => 1,
            'total_budget' => 50000.0,
            'status_distribution' => ['completed' => 5, 'in_progress' => 3],
            'priority_distribution' => ['high' => 2, 'medium' => 6]
        ];

        $dto = ProjectStatisticsDTO::fromArray($stats);

        expect($dto->total_projects)->toBe(10);
        expect($dto->completed_projects)->toBe(5);
        expect($dto->total_budget)->toBe(50000.0);
    });

    it('can calculate completion percentage in ProjectStatisticsDTO', function () {
        $stats = [
            'total_projects' => 10,
            'completed_projects' => 5,
            'in_progress_projects' => 3,
            'overdue_projects' => 1,
            'total_budget' => 50000.0
        ];

        $dto = ProjectStatisticsDTO::fromArray($stats);

        expect($dto->getCompletionPercentage())->toBe(50.0);
        expect($dto->getOverduePercentage())->toBe(10.0);
    });

    it('can convert ProjectStatisticsDTO to API array', function () {
        $stats = [
            'total_projects' => 10,
            'completed_projects' => 5,
            'total_budget' => 50000.0
        ];

        $dto = ProjectStatisticsDTO::fromArray($stats);
        $apiArray = $dto->toApiArray();

        expect($apiArray)->toHaveKey('total_projects');
        expect($apiArray)->toHaveKey('completion_percentage');
        expect($apiArray)->toHaveKey('overdue_percentage');
        expect($apiArray['total_budget'])->toBe('50000.00'); // Formatted as string
    });

    it('can use BaseDTO methods', function () {
        $dto = new ProjectDTO();
        
        $dto->set('name', 'Test Project');
        $dto->set('status', 'planning');

        expect($dto->has('name'))->toBeTrue();
        expect($dto->has('description'))->toBeFalse();
        expect($dto->get('name'))->toBe('Test Project');
        expect($dto->get('nonexistent', 'default'))->toBe('default');

        $array = $dto->toArray();
        expect($array)->toHaveKey('name');
        expect($array)->toHaveKey('status');

        $json = $dto->toJson();
        expect($json)->toBeString();
        expect(json_decode($json, true))->toHaveKey('name');
    });
});
