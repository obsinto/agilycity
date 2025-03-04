<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Secretary;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        $secretary = Secretary::inRandomOrder()->first();
        $department = Department::where('secretary_id', $secretary->id)->inRandomOrder()->first();

        return [
            'expense_type_id' => ExpenseType::inRandomOrder()->first()->id,
            'department_id' => $department->id,
            'secretary_id' => $secretary->id,
            'amount' => $this->faker->randomFloat(2, 100, 50000),
            'expense_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'observation' => $this->faker->optional(0.7)->sentence(),
            'attachment' => $this->faker->optional(0.3)->imageUrl()
        ];
    }
}
