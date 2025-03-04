<?php

namespace Database\Factories;

use App\Models\ExpenseType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseTypeFactory extends Factory
{
    protected $model = ExpenseType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Material de Escritório',
                'Material de Limpeza',
                'Equipamentos',
                'Serviços Terceirizados',
                'Manutenção',
                'Combustível',
                'Alimentação',
                'Transporte',
                'Medicamentos',
                'Material Didático',
                'Utensílios',
                'Mobiliário',
                'Obras e Instalações',
                'Uniformes',
                'Software e Licenças'
            ]),
            'description' => $this->faker->sentence(),
            'active' => true,
        ];
    }
}
