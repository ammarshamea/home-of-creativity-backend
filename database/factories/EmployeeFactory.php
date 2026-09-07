<?php

namespace Database\Factories;

use App\Enums\EmployeeProfession;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'EMP-'.fake()->unique()->numerify('######'),
            'name' => fake()->name(),
            'phone' => fake()->numerify('+963 9## ### ###'),
            'email' => fake()->unique()->safeEmail(),
            'telegram_user_id' => (string) fake()->unique()->numerify('#########'),
            'clickup_user_id' => fake()->optional()->numerify('########'),
            'profession' => EmployeeProfession::Sales,
            'notes' => null,
            'is_active' => true,
        ];
    }

    public function sales(): static
    {
        return $this->state(fn (): array => ['profession' => EmployeeProfession::Sales]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
