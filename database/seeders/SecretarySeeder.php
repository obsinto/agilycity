<?php

namespace Database\Seeders;

use App\Models\Secretary;
use Illuminate\Database\Seeder;

class SecretarySeeder extends Seeder
{
    public function run(): void
    {
        $secretaries = [
            ['name' => 'Agricultura', 'description' => 'Secretaria de Agricultura'],
            ['name' => 'Bem-Estar Social', 'description' => 'Secretaria de Bem-Estar Social'],
            ['name' => 'Educação', 'description' => 'Secretaria de Educação'],
            ['name' => 'Saúde', 'description' => 'Secretaria de Saúde'],
            ['name' => 'Administração', 'description' => 'Secretaria de Administração'],
            ['name' => 'Finanças', 'description' => 'Secretaria de Finanças'],
            ['name' => 'Governo', 'description' => 'Secretaria de Governo'],
            ['name' => 'Planejamento', 'description' => 'Secretaria de Planejamento'],
        ];

        foreach ($secretaries as $secretary) {
            Secretary::create($secretary);
        }
    }
}
