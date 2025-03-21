<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'AgilyMun') }}</title>

    {{-- Carrega CSS e JS principal via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-gray-100 h-screen">
<div class="flex">
    <!-- Sidebar -->
    <div class="w-64 bg-white shadow-md h-screen flex flex-col">
        <div class="p-4 border-b">
            <h1 class="text-2xl font-bold text-center">AgilyCity</h1>
        </div>

        <!-- Perfil do Usuário -->
        <div class="p-4 border-b bg-gray-50">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}"
                             alt="Avatar"
                             class="h-10 w-10 rounded-full object-cover"
                             onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.png') }}';">
                    @else
                        <div
                                class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-900 truncate">
                        {{ auth()->user()->name }}
                    </div>
                    <a href="{{ route('profile.edit') }}" class="text-sm text-blue-600 hover:underline">
                        Editar Perfil
                    </a>
                </div>
            </div>
        </div>

        <!-- Menu de
        Navegação -->
        <nav class="mt-4 flex-1">
            <ul>
                <!-- Dashboard -->
                @can('view dashboard')
                    <li>
                        <a href="{{ auth()->user()->hasAnyRole(['secretary', 'education_secretary']) ? route('secretary.dashboard') : route('mayor.dashboard') }}"
                           class="block py-2 px-4 hover:bg-gray-100 {{ request()->routeIs('dashboard') || request()->routeIs('secretary.dashboard') ? 'bg-gray-100' : '' }}">
                            Dashboard
                        </a>
                    </li>
                @endcan

                <!-- Tipos de Despesas (não visível para mayor) -->
                @can('manage departments')
                    @if(!auth()->user()->hasRole('mayor'))
                        <li>
                            <a href="{{ route('expense-types.index') }}"
                               class="block py-2 px-4 hover:bg-gray-100 {{ request()->routeIs('expense-types.*') ? 'bg-gray-100' : '' }}">
                                Tipos de Despesas
                            </a>
                        </li>
                    @endif
                @endcan

                <!-- Despesas (não visível para mayor) -->
                @can('manage departments')
                    @if(!auth()->user()->hasRole('mayor'))
                        <li>
                            <a href="{{ route('expenses.index') }}"
                               class="block py-2 px-4 hover:bg-gray-100 {{ request()->routeIs('expenses.*') ? 'bg-gray-100' : '' }}">
                                Despesas
                            </a>
                        </li>
                    @endif
                @endcan

                <!-- Análise Escolar -->
                @can('view all schools')
                    <li>
                        <a href="{{ route('reports.students') }}"
                           class="block py-2 px-4 hover:bg-gray-100 {{ request()->routeIs('reports.students') ? 'bg-gray-100' : '' }}">
                            Análise Escolar
                        </a>
                    </li>
                @endcan

                <!-- Relatório Cantina -->
                @can('view cantina report')
                    <li>
                        <a href="{{ route('cantina.report') }}"
                           class="block py-2 px-4 hover:bg-gray-100 {{ request()->routeIs('cantina.report') ? 'bg-gray-100' : '' }}">
                            Relatório Cantina
                        </a>
                    </li>
                @endcan

                <!-- Cadastrar Alunos (apenas para sector_leader de escola) -->
                @if(auth()->user()->hasRole('sector_leader') && auth()->user()->department && auth()->user()->department->is_school)
                    @can('manage students')
                        <li>
                            <a href="{{ route('enrollments.create') }}"
                               class="block py-2 px-4 hover:bg-gray-100 {{ request()->routeIs('enrollments.create') ? 'bg-gray-100' : '' }}">
                                Cadastrar Alunos
                            </a>
                        </li>
                    @endcan
                @endif

                <!-- Associar Líderes (não visível para mayor) -->
                @can('manage users')
                    @if(!auth()->user()->hasRole('mayor'))
                        <li>
                            <a href="{{ route('secretary.sector-leaders') }}"
                               class="block py-2 px-4 hover:bg-gray-100 {{ request()->routeIs('secretary.sector-leaders') ? 'bg-gray-100' : '' }}">
                                Associar Líderes
                            </a>
                        </li>
                    @endif
                @endcan

                <!-- Gerenciar Departamentos (não visível para mayor) -->
                @can('manage departments')
                    @if(!auth()->user()->hasRole('mayor'))
                        <li>
                            <a href="{{ route('secretary.departments') }}"
                               class="block py-2 px-4 hover:bg-gray-100 {{ request()->routeIs('secretary.departments') ? 'bg-gray-100' : '' }}">
                                Gerenciar Departamentos
                            </a>
                        </li>
                    @endif
                @endcan

                <!-- Credenciais Líderes -->
                @can('manage users')
                    <li>
                        <a href="{{ route('credentials.index') }}"
                           class="block py-2 px-4 hover:bg-gray-100 {{ request()->routeIs('credentials.*') ? 'bg-gray-100' : '' }}">
                            Credenciais Líderes
                        </a>
                    </li>
                @endcan

                <!-- Credenciais Secretários -->
                @can('manage secretaries')
                    <li>
                        <a href="{{ route('credentials.index') }}"
                           class="block py-2 px-4 hover:bg-gray-100 {{ request()->routeIs('credentials.*') ? 'bg-gray-100' : '' }}">
                            Credenciais Secretários
                        </a>
                    </li>
                @endcan

                <!-- Gerenciar Secretários -->
                @can('manage secretaries')
                    <li>
                        <a href="{{ route('secretaries.manage') }}"
                           class="block py-2 px-4 hover:bg-gray-100 {{ request()->routeIs('secretaries.manage') ? 'bg-gray-100' : '' }}">
                            Gerenciar Secretários
                        </a>
                    </li>
                @endcan

                <!-- Teto de Gastos -->
                @can('view financial dashboard')
                    <li>
                        <a href="{{ route('spending-caps.index') }}"
                           class="block py-2 px-4 hover:bg-gray-100 {{ request()->routeIs('spending-caps.*') ? 'bg-gray-100' : '' }}">
                            Teto de Gastos
                        </a>
                    </li>
                @endcan
            </ul>
        </nav>

        <!-- Footer com Logout -->
        <div class="border-t p-4">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full text-left text-red-500 hover:bg-gray-100 py-2 px-4 rounded">
                    Sair
                </button>
            </form>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="flex-1 p-10 overflow-y-auto">
        <!-- Mensagens de Alerta -->
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                 role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Conteúdo da Página -->
        @yield('content')
    </div>
</div>

<!-- Scripts da Página -->
@stack('scripts')
</body>
</html>
