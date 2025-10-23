<?php

use App\DTOs\Interfaces\DTOInterface;
use App\DTOs\GenericDTO;
use App\DTOs\Project\CreateProjectDTO;
use App\DTOs\User\UserDTO;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Interface-based DTO Tests', function () {
    
    it('can use interface for type hinting', function () {
        $dto = CreateProjectDTO::fromArray(['name' => 'Test Project']);
        
        expect($dto)->toBeInstanceOf(DTOInterface::class);
        expect($dto->toArray())->toHaveKey('name');
    });

    it('can work with GenericDTO', function () {
        $data = [
            'title' => 'Test Title',
            'content' => 'Test Content',
            'tags' => ['tag1', 'tag2']
        ];

        $dto = GenericDTO::fromArray($data);

        expect($dto)->toBeInstanceOf(DTOInterface::class);
        expect($dto->get('title'))->toBe('Test Title');
        expect($dto->has('title'))->toBeTrue();
        expect($dto->has('nonexistent'))->toBeFalse();
    });

    it('can set and get values in GenericDTO', function () {
        $dto = new GenericDTO();

        $dto->set('name', 'Test Name');
        $dto->set('value', 123);

        expect($dto->get('name'))->toBe('Test Name');
        expect($dto->get('value'))->toBe(123);
        expect($dto->get('nonexistent', 'default'))->toBe('default');
    });

    it('can merge data in GenericDTO', function () {
        $dto = GenericDTO::fromArray(['name' => 'Original']);
        
        $dto->merge(['description' => 'Added']);
        
        expect($dto->get('name'))->toBe('Original');
        expect($dto->get('description'))->toBe('Added');
    });

    it('can get only specific keys from GenericDTO', function () {
        $data = [
            'name' => 'Test',
            'description' => 'Description',
            'tags' => ['tag1'],
            'metadata' => ['key' => 'value']
        ];

        $dto = GenericDTO::fromArray($data);
        $onlyDTO = $dto->only(['name', 'description']);

        expect($onlyDTO->get('name'))->toBe('Test');
        expect($onlyDTO->get('description'))->toBe('Description');
        expect($onlyDTO->has('tags'))->toBeFalse();
        expect($onlyDTO->has('metadata'))->toBeFalse();
    });

    it('can get all except specific keys from GenericDTO', function () {
        $data = [
            'name' => 'Test',
            'description' => 'Description',
            'tags' => ['tag1'],
            'metadata' => ['key' => 'value']
        ];

        $dto = GenericDTO::fromArray($data);
        $exceptDTO = $dto->except(['tags', 'metadata']);

        expect($exceptDTO->get('name'))->toBe('Test');
        expect($exceptDTO->get('description'))->toBe('Description');
        expect($exceptDTO->has('tags'))->toBeFalse();
        expect($exceptDTO->has('metadata'))->toBeFalse();
    });

    it('can check if GenericDTO is empty', function () {
        $emptyDTO = new GenericDTO();
        $filledDTO = GenericDTO::fromArray(['name' => 'Test']);

        expect($emptyDTO->isEmpty())->toBeTrue();
        expect($filledDTO->isEmpty())->toBeFalse();
    });

    it('can count properties in GenericDTO', function () {
        $dto = GenericDTO::fromArray(['name' => 'Test', 'value' => 123]);

        expect($dto->count())->toBe(2);
    });

    it('can remove properties from GenericDTO', function () {
        $dto = GenericDTO::fromArray(['name' => 'Test', 'value' => 123]);
        
        $dto->remove('value');
        
        expect($dto->has('name'))->toBeTrue();
        expect($dto->has('value'))->toBeFalse();
    });

    it('can create UserDTO from model', function () {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $dto = UserDTO::fromModel($user);

        expect($dto)->toBeInstanceOf(DTOInterface::class);
        expect($dto->name)->toBe('Test User');
        expect($dto->email)->toBe('test@example.com');
        expect($dto->id)->toBe($user->id);
    });

    it('can convert UserDTO to API array', function () {
        $user = User::factory()->create();
        $dto = UserDTO::fromModel($user);
        $apiArray = $dto->toApiArray();

        expect($apiArray)->toHaveKey('id');
        expect($apiArray)->toHaveKey('name');
        expect($apiArray)->toHaveKey('email');
        expect($apiArray)->toHaveKey('created_at');
        expect($apiArray)->toHaveKey('updated_at');
        expect($apiArray)->toHaveKey('projects_count');
    });

    it('can get fillable array from UserDTO', function () {
        $dto = UserDTO::fromArray([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'id' => 1,
            'created_at' => '2024-01-01T00:00:00Z'
        ]);

        $fillableArray = $dto->toFillableArray();

        expect($fillableArray)->toHaveKey('name');
        expect($fillableArray)->toHaveKey('email');
        expect($fillableArray)->not->toHaveKey('id');
        expect($fillableArray)->not->toHaveKey('created_at');
    });

    it('can work with multiple DTO types using interface', function () {
        $projectDTO = CreateProjectDTO::fromArray(['name' => 'Project']);
        $userDTO = UserDTO::fromArray(['name' => 'User']);
        $genericDTO = GenericDTO::fromArray(['data' => 'value']);

        $dtos = [$projectDTO, $userDTO, $genericDTO];

        foreach ($dtos as $dto) {
            expect($dto)->toBeInstanceOf(DTOInterface::class);
            expect($dto->toArray())->toBeArray();
            expect($dto->toJson())->toBeString();
        }
    });

    it('can use interface methods consistently across DTOs', function () {
        $projectDTO = CreateProjectDTO::fromArray(['name' => 'Project']);
        $genericDTO = GenericDTO::fromArray(['title' => 'Title']);

        // Both should have the same interface methods
        expect(method_exists($projectDTO, 'toArray'))->toBeTrue();
        expect(method_exists($projectDTO, 'toJson'))->toBeTrue();
        expect(method_exists($projectDTO, 'get'))->toBeTrue();
        expect(method_exists($projectDTO, 'set'))->toBeTrue();
        expect(method_exists($projectDTO, 'has'))->toBeTrue();

        expect(method_exists($genericDTO, 'toArray'))->toBeTrue();
        expect(method_exists($genericDTO, 'toJson'))->toBeTrue();
        expect(method_exists($genericDTO, 'get'))->toBeTrue();
        expect(method_exists($genericDTO, 'set'))->toBeTrue();
        expect(method_exists($genericDTO, 'has'))->toBeTrue();
    });
});
