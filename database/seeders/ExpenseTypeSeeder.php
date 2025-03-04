<?php

namespace Database\Seeders;

use App\Models\ExpenseType;
use Illuminate\Database\Seeder;

class ExpenseTypeSeeder extends Seeder
{
    public function run(): void
    {
        $expenseTypes = [
            ['name' => 'Alimentação', 'description' => 'Despesas com alimentação'],
            ['name' => 'Transporte', 'description' => 'Despesas com transporte'],
            ['name' => 'Infraestrutura', 'description' => 'Despesas com infraestrutura'],
            ['name' => 'Educação', 'description' => 'Despesas com educação'],
            ['name' => 'Saúde', 'description' => 'Despesas com saúde'],
            ['name' => 'Serviços', 'description' => 'Despesas com prestação de serviços'],
            ['name' => 'Manutenção', 'description' => 'Despesas com manutenção de equipamentos e estruturas'],
            ['name' => 'Tecnologia', 'description' => 'Despesas com tecnologia e inovação'],
        ];

        foreach ($expenseTypes as $expenseType) {
            ExpenseType::create($expenseType);
        }
    }
}
