<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpa o cache de permissões
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Criação das Permissões
        Permission::create(['name' => 'view dashboard']);
        Permission::create(['name' => 'manage secretaries']);
        Permission::create(['name' => 'manage departments']);
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'view financial dashboard']);
        Permission::create(['name' => 'view all schools']);
        Permission::create(['name' => 'manage students']); // Permissão para gerenciar alunos
        Permission::create(['name' => 'view cantina report']); // Permissão para ver relatório da cantina

        // Criação dos Roles
        $mayor = Role::create(['name' => 'mayor']);
        $secretaryEducation = Role::create(['name' => 'education_secretary']);
        $secretaryEducation->givePermissionTo([
            'view dashboard',
            'manage departments',
            'manage users',
            'view all schools',
            'manage students', // Permissão para gerenciar alunos
            'view cantina report' // Permissão para ver relatório da cantina
        ]);
        $mayor->givePermissionTo(Permission::all());

        $secretaryRole = Role::create(['name' => 'secretary']);
        $secretaryRole->givePermissionTo(['view dashboard', 'manage departments', 'manage users']);

        $sectorLeader = Role::create(['name' => 'sector_leader']);
        $sectorLeader->givePermissionTo(['view dashboard']);

        // Criação do usuário do Prefeito
        User::create([
            'name' => 'Admin Prefeito',
            'email' => 'prefeito@agilytech.com',
            'password' => Hash::make('senha123')
        ])->assignRole('mayor');

        // Array padrão de secretarias com descrição
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

        // Mapeamento dos departamentos para cada secretaria
        $departmentsMapping = [
            'Agricultura' => ['Sede', 'Parque', 'Mercadão'],
            'Bem-Estar Social' => ['Secretaria/Sede', 'CRAS', 'Bolsa Família', 'Criança Feliz', 'Conselho Tutelar', 'Projeto Conviver'],
            'Educação' => ['Sede', 'Escolas do Campo', 'Infantil', 'Creche', 'Walter', 'Padre Otacílio', 'Almerinda Catarina', 'Ana Silva', 'Ângelo Magalhães', 'Nelito Fonseca', 'Biblioteca', 'Cantina Central', 'Centro de Cultura', 'Almoxarifado'],
            'Saúde' => ['Sede', 'Posto Neli Amaral', 'Posto Adenilson Rosa', 'Posto Iara Pinto', 'Posto da Malvina', 'Posto Palmeira dos Gois', 'Hospital', 'Centro de Imagem', 'SAMU'],
            'Administração' => ['Sede', 'Arquivo', 'Guarda Municipal', 'INSS', 'Cultura', 'Almoxarifado Central'],
            'Finanças' => ['Sede', 'Tesouraria e Tributos', 'Contabilidade'],
            'Governo' => ['Sede', 'Controle Interno'],
            'Planejamento' => ['Sede'],
        ];

        // Criação das Secretarias, departamentos e líderes
        foreach ($secretaries as $secData) {
            // Cria a secretaria
            $secretary = Secretary::create([
                'name' => $secData['name'],
                'description' => $secData['description'],
            ]);

            // Gera um domínio a partir do nome da secretaria
            $secretaryDomain = strtolower(str_replace(['ç', 'ã', ' ', '-'], ['c', 'a', '', ''], $secData['name'])) . '.com';

            // Cria o usuário para o Secretário
            if ($secData['name'] === 'Educação') {
                User::create([
                    'name' => "Secretário de " . $secData['name'],
                    'email' => "secretario@{$secretaryDomain}",
                    'password' => Hash::make('senha123'),
                    'secretary_id' => $secretary->id,
                ])->assignRole('education_secretary'); // Atribui o papel education_secretary
            } else {
                User::create([
                    'name' => "Secretário de " . $secData['name'],
                    'email' => "{$secretaryDomain}@{$secretaryDomain}",
                    'password' => Hash::make('senha123'),
                    'secretary_id' => $secretary->id,
                ])->assignRole('secretary');
            }

            // Verifica se há departamentos definidos para essa secretaria
            if (isset($departmentsMapping[$secData['name']])) {
                foreach ($departmentsMapping[$secData['name']] as $deptName) {
                    // Define se o departamento é uma escola
                    $isSchool = false;
                    if ($secData['name'] === 'Educação') {
                        $naoEscola = ['Cantina Central', 'Biblioteca', 'Centro de Cultura', 'Almoxarifado'];
                        if (!in_array($deptName, $naoEscola)) {
                            $isSchool = true;
                        }
                    }

                    // Cria o departamento com o campo is_school
                    $department = Department::create([
                        'name' => $deptName,
                        'secretary_id' => $secretary->id,
                        'is_school' => $isSchool,
                    ]);

                    // Gera o prefixo de e-mail para o líder
                    $deptEmailPrefix = strtolower(str_replace(
                        ['ç', 'ã', 'á', 'í', 'ó', 'é', ' ', '-', '/'],
                        ['c', 'a', 'a', 'i', 'o', 'e', '', '', ''],
                        $deptName
                    ));

                    // Cria o usuário líder do departamento
                    User::create([
                        'name' => "Líder de " . $deptName,
                        'email' => "{$deptEmailPrefix}@{$secretaryDomain}",
                        'password' => Hash::make('senha123'),
                        'department_id' => $department->id,
                        'secretary_id' => $secretary->id,
                    ])->assignRole('sector_leader');
                }
            }
        }
    }
}
