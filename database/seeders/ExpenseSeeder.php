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

            // Valor exato para cada secretaria: R$ 10.000
            $totalSecretaryBudget = 10000.00;

            // Distribuir o orçamento entre os departamentos da secretaria
            $departmentCount = $departments->count();
            if ($departmentCount > 0) {
                $budgetPerDepartment = $totalSecretaryBudget / $departmentCount;

                foreach ($departments as $department) {
                    // Número de despesas por departamento (entre 4 e 8)
                    $numExpenses = rand(4, 8);

                    // Distribuir o orçamento do departamento entre as despesas
                    $remainingBudget = $budgetPerDepartment;

                    for ($i = 0; $i < $numExpenses; $i++) {
                        // Seleciona um tipo de despesa aleatório
                        $expenseType = $expenseTypes->random();

                        // Gera uma data aleatória nos últimos 6 meses
                        $expenseDate = Carbon::now()->subDays(rand(0, 180))->toDateString();

                        // Para a última despesa, usar o valor exato restante
                        if ($i == $numExpenses - 1) {
                            $amount = $remainingBudget;
                        } else {
                            // Caso contrário, gerar um valor aleatório que não exceda o restante
                            $maxAmount = $remainingBudget * 0.8; // No máximo 80% do restante
                            $amount = $faker->randomFloat(2, 50, max(50, $maxAmount));
                            $remainingBudget -= $amount;
                        }

                        Expense::create([
                            'expense_type_id' => $expenseType->id,
                            'department_id' => $department->id,
                            'secretary_id' => $secretary->id,
                            'amount' => $amount,
                            'expense_date' => $expenseDate,
                            'observation' => $faker->sentence(),
                            'attachment' => null,
                        ]);
                    }
                }
            }
        }

        // Confirmação de que os dados foram criados
        $this->command->info('Despesas geradas com sucesso!');
        $this->command->info('Cada secretaria tem exatamente R$ 10.000 em despesas.');
    }
}
