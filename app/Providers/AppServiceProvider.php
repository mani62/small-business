<?php

namespace App\Providers;

use App\Models\Task;
use App\Observers\TaskObserver;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Repositories\TaskRepository;
use App\Services\Auth\AuthService;
use App\Services\Project\ProjectService;
use App\Services\Task\TaskService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register services as singletons
        $this->app->singleton(AuthService::class);
        $this->app->singleton(ProjectService::class);
        $this->app->singleton(TaskService::class);
        
        // Register repository bindings
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        Task::observe(TaskObserver::class);
    }
}
