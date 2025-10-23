<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 year', '+1 year');
        $endDate = $this->faker->dateTimeBetween($startDate, '+2 years');

        return [
            'name' => $this->faker->words(3, true) . ' Project',
            'description' => $this->faker->paragraph(3),
            'status' => $this->faker->randomElement(['planning', 'in_progress', 'on_hold', 'completed', 'cancelled']),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'budget' => $this->faker->randomFloat(2, 100, 100000),
            'user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the project is in planning status.
     */
    public function planning(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'planning',
        ]);
    }

    /**
     * Indicate that the project is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
        ]);
    }

    /**
     * Indicate that the project is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the project is on hold.
     */
    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'on_hold',
        ]);
    }

    /**
     * Indicate that the project is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Indicate that the project has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    /**
     * Indicate that the project has urgent priority.
     */
    public function urgentPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }

    /**
     * Indicate that the project has low priority.
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'low',
        ]);
    }

    /**
     * Indicate that the project is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'end_date' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
            'status' => $this->faker->randomElement(['planning', 'in_progress', 'on_hold']),
        ]);
    }

    /**
     * Indicate that the project has no budget.
     */
    public function noBudget(): static
    {
        return $this->state(fn (array $attributes) => [
            'budget' => null,
        ]);
    }

    /**
     * Indicate that the project has no dates.
     */
    public function noDates(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => null,
            'end_date' => null,
        ]);
    }
}

