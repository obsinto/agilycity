<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Secretary;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        // Obtém todos os tipos de despesas criados
        $expenseTypes = ExpenseType::all();

        // Obtém todas as secretarias (geradas no seu seeder de estrutura)
        $secretaries = Secretary::all();

        foreach ($secretaries as $secretary) {
            // Obtém os departamentos da secretaria
            $departments = $secretary->departments;

            foreach ($departments as $department) {
                // Gera um número aleatório de despesas para cada departamento (ex.: 10 a 20)
                $numExpenses = rand(10, 20);

                for ($i = 0; $i < $numExpenses; $i++) {
                    // Seleciona um tipo de despesa aleatório
                    $expenseType = $expenseTypes->random();

                    // Gera uma data aleatória nos últimos 6 meses
                    $expenseDate = Carbon::now()->subDays(rand(0, 180))->toDateString();

                    Expense::create([
                        'expense_type_id' => $expenseType->id,
                        'department_id' => $department->id,
                        'secretary_id' => $secretary->id,
                        'amount' => $faker->randomFloat(2, 50, 5000), // valor entre 50 e 5000
                        'expense_date' => $expenseDate,
                        'observation' => $faker->sentence(),
                        'attachment' => null,
                    ]);
                }
            }
        }
    }
}
