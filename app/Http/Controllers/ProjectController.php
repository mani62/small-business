<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use App\Services\Project\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    protected ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    /**
     * Display a listing of the user's projects.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $result = $this->projectService->getProjects($request->user(), $request);

            return response()->json([
                'message' => 'Projects retrieved successfully',
                'data' => [
                    'data' => $result['data']
                ],
                'meta' => $result['meta']
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve projects',
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Store a newly created project.
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        try {
            $result = $this->projectService->createProject($request->user(), $request->validated(), $request);

            return response()->json([
                'message' => 'Project created successfully',
                'data' => $result
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create project',
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified project.
     */
    public function show(Request $request, Project $project): JsonResponse
    {
        try {
            $result = $this->projectService->getProject($request->user(), $project, $request);

            return response()->json([
                'message' => 'Project retrieved successfully',
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 404 ? 404 : 500;
            return response()->json([
                'message' => 'Failed to retrieve project',
                'error' => $statusCode === 404 ? 'Project not found' : 'Internal server error'
            ], $statusCode);
        }
    }

    /**
     * Update the specified project.
     */
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        try {
            $result = $this->projectService->updateProject($request->user(), $project, $request->validated(), $request);

            return response()->json([
                'message' => 'Project updated successfully',
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 404 ? 404 : 500;
            return response()->json([
                'message' => 'Failed to update project',
                'error' => $statusCode === 404 ? 'Project not found' : 'Internal server error'
            ], $statusCode);
        }
    }

    /**
     * Remove the specified project from storage (soft delete).
     */
    public function destroy(Request $request, Project $project): JsonResponse
    {
        try {
            $this->projectService->deleteProject($request->user(), $project, $request);

            return response()->json([
                'message' => 'Project deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 404 ? 404 : 500;
            return response()->json([
                'message' => 'Failed to delete project',
                'error' => $statusCode === 404 ? 'Project not found' : 'Internal server error'
            ], $statusCode);
        }
    }

    /**
     * Restore a soft-deleted project.
     */
    public function restore(Request $request, int $id): JsonResponse
    {
        try {
            $result = $this->projectService->restoreProject($request->user(), $id, $request);

            return response()->json([
                'message' => 'Project restored successfully',
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 404 ? 404 : 500;
            return response()->json([
                'message' => 'Failed to restore project',
                'error' => $statusCode === 404 ? 'Project not found' : 'Internal server error'
            ], $statusCode);
        }
    }

    /**
     * Permanently delete a project.
     */
    public function forceDelete(Request $request, int $id): JsonResponse
    {
        try {
            $this->projectService->forceDeleteProject($request->user(), $id, $request);

            return response()->json([
                'message' => 'Project permanently deleted'
            ], 200);

        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 404 ? 404 : 500;
            return response()->json([
                'message' => 'Failed to permanently delete project',
                'error' => $statusCode === 404 ? 'Project not found' : 'Internal server error'
            ], $statusCode);
        }
    }

    /**
     * Get project statistics for the authenticated user.
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $result = $this->projectService->getProjectStatistics($request->user(), $request);

            return response()->json([
                'message' => 'Project statistics retrieved successfully',
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve project statistics',
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Search projects by name or description.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $searchTerm = $request->get('q', '');
            $result = $this->projectService->searchProjects($request->user(), $searchTerm, $request);

            return response()->json([
                'message' => 'Search completed successfully',
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 400 ? 400 : 500;
            return response()->json([
                'message' => 'Failed to search projects',
                'error' => $statusCode === 400 ? $e->getMessage() : 'Internal server error'
            ], $statusCode);
        }
    }

    /**
     * Get projects by status.
     */
    public function byStatus(Request $request, string $status): JsonResponse
    {
        try {
            $result = $this->projectService->getProjectsByStatus($request->user(), $status, $request);

            return response()->json([
                'message' => "Projects with status '{$status}' retrieved successfully",
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 400 ? 400 : 500;
            return response()->json([
                'message' => 'Failed to retrieve projects by status',
                'error' => $statusCode === 400 ? $e->getMessage() : 'Internal server error'
            ], $statusCode);
        }
    }

    /**
     * Get overdue projects.
     */
    public function overdue(Request $request): JsonResponse
    {
        try {
            $result = $this->projectService->getOverdueProjects($request->user(), $request);

            return response()->json([
                'message' => 'Overdue projects retrieved successfully',
                'data' => $result['data'],
                'count' => $result['count']
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve overdue projects',
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Bulk update project status.
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'project_ids' => 'required|array|min:1',
                'project_ids.*' => 'integer|exists:projects,id',
                'status' => 'required|string|in:planning,in_progress,on_hold,completed,cancelled'
            ]);

            $result = $this->projectService->bulkUpdateStatus(
                $request->user(),
                $request->project_ids,
                $request->status,
                $request
            );

            return response()->json([
                'message' => $result['message'],
                'updated_count' => $result['updated_count']
            ], 200);

        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 404 ? 404 : 500;
            return response()->json([
                'message' => 'Failed to bulk update project status',
                'error' => $statusCode === 404 ? $e->getMessage() : 'Internal server error'
            ], $statusCode);
        }
    }

    /**
     * Duplicate a project.
     */
    public function duplicate(Request $request, Project $project): JsonResponse
    {
        try {
            $result = $this->projectService->duplicateProject($request->user(), $project, $request);

            return response()->json([
                'message' => 'Project duplicated successfully',
                'data' => $result
            ], 201);

        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 404 ? 404 : 500;
            return response()->json([
                'message' => 'Failed to duplicate project',
                'error' => $statusCode === 404 ? 'Project not found' : 'Internal server error'
            ], $statusCode);
        }
    }
}