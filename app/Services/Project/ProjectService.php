<?php

namespace App\Services\Project;

use App\DTOs\Project\CreateProjectDTO;
use App\DTOs\Project\ProjectDTO;
use App\DTOs\Project\ProjectListDTO;
use App\DTOs\Project\ProjectStatisticsDTO;
use App\DTOs\Project\UpdateProjectDTO;
use App\Models\Project;
use App\Models\User;
use App\Services\Project\Enums\ProjectPriority;
use App\Services\Project\Enums\ProjectStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectService
{
    /**
     * Display a listing of the user's projects.
     *
     * @param User $user
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function getProjects(User $user, Request $request): array
    {
        try {
            $query = $user->projects()->withTrashed();


            if ($request->has('status')) {
                $status = $request->status;
                if (in_array($status, ProjectStatus::getValues())) {
                    $query->where('status', $status);
                }
            }

            if ($request->has('priority')) {
                $priority = $request->priority;
                if (in_array($priority, ProjectPriority::getValues())) {
                    $query->where('priority', $priority);
                }
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $allowedSortFields = ['name', 'status', 'priority', 'start_date', 'end_date', 'created_at', 'updated_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $perPage = min($request->get('per_page', 15), 100);
            $projects = $query->paginate($perPage);

            $projects->getCollection()->transform(function ($project) {
                $project->progress_percentage = $this->getProjectProgressPercentage($project);
                $project->is_overdue = $this->isProjectOverdue($project);
                return $project;
            });

            $projectListDTO = ProjectListDTO::fromPaginator($projects);

            return $projectListDTO->toApiArray();

        } catch (\Exception $e) {
            Log::error('Failed to retrieve projects', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Store a newly created project.
     *
     * @param User $user
     * @param array $validatedData
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function createProject(User $user, array $validatedData, Request $request): array
    {
        try {
            DB::beginTransaction();

            $validatedData['user_id'] = $user->id;

            $createProjectDTO = CreateProjectDTO::fromRequest($validatedData);
            $project = Project::create($createProjectDTO->toDatabaseArray());
            $project->load('user');

            $project->progress_percentage = $this->getProjectProgressPercentage($project);
            $project->is_overdue = $this->isProjectOverdue($project);

            $projectDTO = ProjectDTO::fromModel($project);

            DB::commit();

            Log::info('Project created successfully', [
                'project_id' => $project->id,
                'user_id' => $user->id,
                'project_name' => $project->name,
                'ip' => $request->ip()
            ]);

            return $projectDTO->toApiArray();

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create project', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Display the specified project.
     *
     * @param User $user
     * @param Project $project
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function getProject(User $user, Project $project, Request $request): array
    {
        try {
            if ($project->user_id !== $user->id) {
                throw new \Exception('Project not found', 404);
            }

            $project->load('user');
            
            $project->progress_percentage = $this->getProjectProgressPercentage($project);
            $project->is_overdue = $this->isProjectOverdue($project);

            $projectDTO = ProjectDTO::fromModel($project);

            return $projectDTO->toApiArray();

        } catch (\Exception $e) {
            Log::error('Failed to retrieve project', [
                'error' => $e->getMessage(),
                'project_id' => $project->id ?? null,
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Update the specified project.
     *
     * @param User $user
     * @param Project $project
     * @param array $validatedData
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function updateProject(User $user, Project $project, array $validatedData, Request $request): array
    {
        try {
            if ($project->user_id !== $user->id) {
                throw new \Exception('Project not found', 404);
            }

            DB::beginTransaction();

            $updateProjectDTO = UpdateProjectDTO::fromRequest($validatedData);
            
            if ($updateProjectDTO->hasUpdates()) {
                $project->update($updateProjectDTO->toUpdateArray());
            }
            
            $project->load('user');

            $project->progress_percentage = $this->getProjectProgressPercentage($project);
            $project->is_overdue = $this->isProjectOverdue($project);

            $projectDTO = ProjectDTO::fromModel($project);

            DB::commit();

            Log::info('Project updated successfully', [
                'project_id' => $project->id,
                'user_id' => $user->id,
                'project_name' => $project->name,
                'ip' => $request->ip()
            ]);

            return $projectDTO->toApiArray();

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update project', [
                'error' => $e->getMessage(),
                'project_id' => $project->id ?? null,
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Remove the specified project from storage (soft delete).
     *
     * @param User $user
     * @param Project $project
     * @param Request $request
     * @return void
     * @throws \Exception
     */
    public function deleteProject(User $user, Project $project, Request $request): void
    {
        try {
            if ($project->user_id !== $user->id) {
                throw new \Exception('Project not found', 404);
            }

            DB::beginTransaction();

            $project->delete();

            DB::commit();

            Log::info('Project deleted successfully', [
                'project_id' => $project->id,
                'user_id' => $user->id,
                'project_name' => $project->name,
                'ip' => $request->ip()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete project', [
                'error' => $e->getMessage(),
                'project_id' => $project->id ?? null,
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Restore a soft-deleted project.
     *
     * @param User $user
     * @param int $projectId
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function restoreProject(User $user, int $projectId, Request $request): array
    {
        try {
            $project = Project::withTrashed()->find($projectId);

            if (!$project) {
                throw new \Exception('Project not found', 404);
            }

            if ($project->user_id !== $user->id) {
                throw new \Exception('Project not found', 404);
            }

            DB::beginTransaction();

            $project->restore();
            $project->load('user');

            $project->progress_percentage = $this->getProjectProgressPercentage($project);
            $project->is_overdue = $this->isProjectOverdue($project);

            $projectDTO = ProjectDTO::fromModel($project);

            DB::commit();

            Log::info('Project restored successfully', [
                'project_id' => $project->id,
                'user_id' => $user->id,
                'project_name' => $project->name,
                'ip' => $request->ip()
            ]);

            return $projectDTO->toApiArray();

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to restore project', [
                'error' => $e->getMessage(),
                'project_id' => $projectId,
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Permanently delete a project.
     *
     * @param User $user
     * @param int $projectId
     * @param Request $request
     * @return void
     * @throws \Exception
     */
    public function forceDeleteProject(User $user, int $projectId, Request $request): void
    {
        try {
            $project = Project::withTrashed()->find($projectId);

            if (!$project) {
                throw new \Exception('Project not found', 404);
            }

            if ($project->user_id !== $user->id) {
                throw new \Exception('Project not found', 404);
            }

            DB::beginTransaction();

            $projectName = $project->name;
            $project->forceDelete();

            DB::commit();

            Log::info('Project permanently deleted', [
                'project_id' => $projectId,
                'user_id' => $user->id,
                'project_name' => $projectName,
                'ip' => $request->ip()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to permanently delete project', [
                'error' => $e->getMessage(),
                'project_id' => $projectId,
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Get project statistics for the authenticated user.
     *
     * @param User $user
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function getProjectStatistics(User $user, Request $request): array
    {
        try {
            $stats = [
                'total_projects' => $user->projects()->count(),
                'completed_projects' => $user->projects()->where('status', ProjectStatus::COMPLETED->value)->count(),
                'in_progress_projects' => $user->projects()->where('status', ProjectStatus::IN_PROGRESS->value)->count(),
                'overdue_projects' => $user->projects()
                    ->where('end_date', '<', now())
                    ->where('status', '!=', ProjectStatus::COMPLETED->value)
                    ->count(),
                'total_budget' => $user->projects()->sum('budget'),
                'status_distribution' => $user->projects()
                    ->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray(),
                'priority_distribution' => $user->projects()
                    ->selectRaw('priority, COUNT(*) as count')
                    ->groupBy('priority')
                    ->pluck('count', 'priority')
                    ->toArray(),
            ];

            $statisticsDTO = ProjectStatisticsDTO::fromArray($stats);

            return $statisticsDTO->toApiArray();

        } catch (\Exception $e) {
            Log::error('Failed to retrieve project statistics', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Search projects by name or description.
     *
     * @param User $user
     * @param string $searchTerm
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function searchProjects(User $user, string $searchTerm, Request $request): array
    {
        try {
            if (empty($searchTerm)) {
                throw new \Exception('Search term is required', 400);
            }

            $projects = $user->projects()
                ->where(function ($query) use ($searchTerm) {
                    $query->where('name', 'like', "%{$searchTerm}%")
                          ->orWhere('description', 'like', "%{$searchTerm}%");
                })
                ->limit(20)
                ->get();

            $projects->transform(function ($project) {
                $project->progress_percentage = $this->getProjectProgressPercentage($project);
                $project->is_overdue = $this->isProjectOverdue($project);
                return $project;
            });

            $projectDTOs = $projects->map(fn($project) => ProjectDTO::fromModel($project));

            return $projectDTOs->map(fn($dto) => $dto->toApiArray())->toArray();

        } catch (\Exception $e) {
            Log::error('Failed to search projects', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'search_term' => $searchTerm,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Get projects by status.
     *
     * @param User $user
     * @param string $status
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function getProjectsByStatus(User $user, string $status, Request $request): array
    {
        try {
            if (!in_array($status, ProjectStatus::getValues())) {
                throw new \Exception('Invalid status provided', 400);
            }

            $projects = $user->projects()
                ->where('status', $status)
                ->get();

            $projects->transform(function ($project) {
                $project->progress_percentage = $this->getProjectProgressPercentage($project);
                $project->is_overdue = $this->isProjectOverdue($project);
                return $project;
            });

            $projectDTOs = $projects->map(fn($project) => ProjectDTO::fromModel($project));

            return $projectDTOs->map(fn($dto) => $dto->toApiArray())->toArray();

        } catch (\Exception $e) {
            Log::error('Failed to retrieve projects by status', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'status' => $status,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Get overdue projects.
     *
     * @param User $user
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function getOverdueProjects(User $user, Request $request): array
    {
        try {
            $overdueProjects = $user->projects()
                ->where('end_date', '<', now())
                ->where('status', '!=', ProjectStatus::COMPLETED->value)
                ->get();

            $overdueProjects->transform(function ($project) {
                $project->progress_percentage = $this->getProjectProgressPercentage($project);
                $project->is_overdue = $this->isProjectOverdue($project);
                return $project;
            });

            $projectDTOs = $overdueProjects->map(fn($project) => ProjectDTO::fromModel($project));

            return [
                'data' => $projectDTOs->map(fn($dto) => $dto->toApiArray())->toArray(),
                'count' => $overdueProjects->count()
            ];

        } catch (\Exception $e) {
            Log::error('Failed to retrieve overdue projects', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Bulk update project status.
     *
     * @param User $user
     * @param array $projectIds
     * @param string $status
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function bulkUpdateStatus(User $user, array $projectIds, string $status, Request $request): array
    {
        try {
            if (!in_array($status, ProjectStatus::getValues())) {
                throw new \Exception('Invalid status provided', 400);
            }

            $userProjects = $user->projects()->whereIn('id', $projectIds)->pluck('id');
            if ($userProjects->count() !== count($projectIds)) {
                throw new \Exception('Some projects not found or do not belong to you', 404);
            }

            DB::beginTransaction();

            $updatedCount = Project::whereIn('id', $projectIds)->update(['status' => $status]);

            DB::commit();

            Log::info('Projects bulk status updated', [
                'user_id' => $user->id,
                'project_ids' => $projectIds,
                'status' => $status,
                'updated_count' => $updatedCount,
                'ip' => $request->ip()
            ]);

            return [
                'updated_count' => $updatedCount,
                'message' => "Successfully updated {$updatedCount} projects to '{$status}' status"
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to bulk update project status', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Duplicate a project.
     *
     * @param User $user
     * @param Project $project
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function duplicateProject(User $user, Project $project, Request $request): array
    {
        try {
            if ($project->user_id !== $user->id) {
                throw new \Exception('Project not found', 404);
            }

            DB::beginTransaction();

            $newProject = $project->replicate();
            $newProject->name = $project->name . ' (Copy)';
            $newProject->status = ProjectStatus::PLANNING->value;
            $newProject->created_at = now();
            $newProject->updated_at = now();
            $newProject->save();

            $newProject->load('user');

            $newProject->progress_percentage = $this->getProjectProgressPercentage($newProject);
            $newProject->is_overdue = $this->isProjectOverdue($newProject);

            $projectDTO = ProjectDTO::fromModel($newProject);

            DB::commit();

            Log::info('Project duplicated successfully', [
                'original_project_id' => $project->id,
                'new_project_id' => $newProject->id,
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            return $projectDTO->toApiArray();

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to duplicate project', [
                'error' => $e->getMessage(),
                'project_id' => $project->id ?? null,
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Get project progress percentage based on status.
     *
     * @param Project $project
     * @return int
     */
    private function getProjectProgressPercentage(Project $project): int
    {
        $status = ProjectStatus::tryFrom($project->status);
        return $status ? $status->getProgressPercentage() : 0;
    }

    /**
     * Check if project is overdue.
     *
     * @param Project $project
     * @return bool
     */
    private function isProjectOverdue(Project $project): bool
    {
        return $project->end_date && 
               $project->end_date->isPast() && 
               $project->status !== ProjectStatus::COMPLETED->value;
    }
}
