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

        // Novas permissões
        Permission::create(['name' => 'manage expense types']); // Gerenciar tipos de despesa
        Permission::create(['name' => 'manage expenses']); // Gerenciar despesas
        Permission::create(['name' => 'view student report']); // Visualizar relatório de alunos
        Permission::create(['name' => 'manage monthly meals']); // Gerenciar merenda mensal

        // Criação dos Roles
        $mayor = Role::create(['name' => 'mayor']);
        $secretaryEducation = Role::create(['name' => 'education_secretary']);
        $secretaryEducation->givePermissionTo([
            'view dashboard',
            'manage departments',
            'manage users',
            'view all schools',
            'manage students',
            'view cantina report',
            'manage expense types',
            'manage expenses',
            'view student report',
            'manage monthly meals'
        ]);
        $mayor->givePermissionTo(Permission::all());

        $secretaryRole = Role::create(['name' => 'secretary']);
        $secretaryRole->givePermissionTo([
            'view dashboard',
            'manage departments',
            'manage users',
            'manage expense types',
            'manage expenses'
        ]);

        $sectorLeader = Role::create(['name' => 'sector_leader']);
        $sectorLeader->givePermissionTo([
            'view dashboard',
            'manage expense types',
            'manage expenses'
        ]);

        // Role para líderes de escolas
        $schoolLeaderRole = Role::create(['name' => 'school_leader']);
        $schoolLeaderRole->givePermissionTo([
            'view dashboard',
            'manage expense types',
            'manage expenses',
            'view cantina report',
            'manage students',
            'view student report'
        ]);

        // Role para líder da cantina central
        $cantinaLeaderRole = Role::create(['name' => 'cantina_leader']);
        $cantinaLeaderRole->givePermissionTo([
            'view dashboard',
            'manage expense types',
            'manage expenses',
            'view cantina report',
            'manage monthly meals'
        ]);

        // Role para secretários administrativos (assistentes)
        $secretaryAssistantRole = Role::create(['name' => 'secretary_assistant']);
        $secretaryAssistantRole->givePermissionTo(['view dashboard']);

        // Criação do usuário do Prefeito
        User::create([
            'name' => 'Admin Prefeito',
            'email' => 'prefeito@agilytech.com',
            'password' => Hash::make('senha123'),
            'secretary_id' => null,
            'department_id' => null
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
            $domainAbbr = $this->getDomainAbbreviation($secData['name']);
            $secretaryDomain = "{$domainAbbr}.com";

            // Cria o usuário para o Secretário
            if ($secData['name'] === 'Educação') {
                User::create([
                    'name' => "Secretário de " . $secData['name'],
                    'email' => "secretario@{$domainAbbr}.com",
                    'password' => Hash::make('senha123'),
                    'secretary_id' => $secretary->id,
                    'department_id' => null
                ])->assignRole('education_secretary'); // Atribui o papel education_secretary
            } else {
                User::create([
                    'name' => "Secretário de " . $secData['name'],
                    'email' => "secretario@{$domainAbbr}.com",
                    'password' => Hash::make('senha123'),
                    'secretary_id' => $secretary->id,
                    'department_id' => null
                ])->assignRole('secretary');
            }

            // Cria o usuário para o secretário administrativo (assistente)
            User::create([
                'name' => "Secretário Administrativo de " . $secData['name'],
                'email' => "assistente@{$domainAbbr}.com",
                'password' => Hash::make('senha123'),
                'secretary_id' => $secretary->id,
                'department_id' => null
            ])->assignRole('secretary_assistant');

            // Verifica se há departamentos definidos para essa secretaria
            if (isset($departmentsMapping[$secData['name']])) {
                foreach ($departmentsMapping[$secData['name']] as $deptName) {
                    // Define se o departamento é uma escola
                    $isSchool = false;

                    if ($secData['name'] === 'Educação') {
                        $naoEscola = ['Sede', 'Cantina Central', 'Biblioteca', 'Centro de Cultura', 'Almoxarifado'];
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

                    // Se o departamento for "Almerinda Catarina" ou "Sede", use o formato específico
                    if ($deptName === 'Almerinda Catarina') {
                        $leaderEmail = "almerinda@{$domainAbbr}.com";
                    } elseif ($deptName === 'Sede') {
                        $leaderEmail = "sede@{$domainAbbr}.com";
                    } else {
                        $leaderEmail = "{$deptEmailPrefix}@{$domainAbbr}.com";
                    }

                    // Cria o usuário líder do departamento com base no tipo
                    $user = User::create([
                        'name' => "Líder de " . $deptName,
                        'email' => $leaderEmail,
                        'password' => Hash::make('senha123'),
                        'secretary_id' => $secretary->id,
                        'department_id' => $department->id
                    ]);

                    // Atribui o papel apropriado baseado no tipo de departamento

                    if ($secData['name'] === 'Educação' && $deptName === 'Cantina Central') {
                        $user->assignRole('cantina_leader');
                    } elseif ($isSchool) {
                        $user->assignRole('school_leader');
                    } else {
                        $user->assignRole('sector_leader');
                    }
                }
            }
        }
    }

    /**
     * Retorna a abreviação do domínio com base no nome da secretaria
     */
    private function getDomainAbbreviation(string $secretaryName): string
    {
        $abbrs = [
            'Agricultura' => 'agri',
            'Bem-Estar Social' => 'stbes',
            'Educação' => 'educ',
            'Saúde' => 'saude',
            'Administração' => 'adm',
            'Finanças' => 'fin',
            'Governo' => 'gov',
            'Planejamento' => 'plan',
        ];

        return $abbrs[$secretaryName] ?? strtolower(str_replace(['ç', 'ã', ' ', '-'], ['c', 'a', '', ''], $secretaryName));
    }
}
