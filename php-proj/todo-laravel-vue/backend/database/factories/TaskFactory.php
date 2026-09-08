<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{

    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->name(),
            'description' => fake()->text(),
            'due_date' => fake()->date(),
            'status' => 0,
            'priority' => rand(1,4),
            'is_archived' =>  rand(0,1),
            'order' =>  rand(0,100)
        ];
    }
}
