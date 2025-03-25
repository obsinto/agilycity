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
<div x-data="{ sidebarOpen: window.innerWidth >= 768, mobileMenuOpen: false }" 
     @resize.window="sidebarOpen = window.innerWidth >= 768 ? sidebarOpen : false; if (window.innerWidth >= 768) mobileMenuOpen = false;" 
     class="flex h-screen bg-gray-100">
    
    <!-- Sidebar - Visível apenas em desktop -->
    <div :class="{
             'w-64': sidebarOpen && window.innerWidth >= 768, 
             'w-16': !sidebarOpen && window.innerWidth >= 768,
             'hidden': window.innerWidth < 768
         }" 
         class="bg-white shadow-md h-screen transition-all duration-300 ease-in-out flex flex-col">
        
        <div class="p-4 border-b flex justify-between items-center">
            <h1 class="text-2xl font-bold" :class="{'hidden': !sidebarOpen}">AgilyCity</h1>
            <h1 class="text-2xl font-bold" :class="{'hidden': sidebarOpen}">AC</h1>
            <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" x-show="!sidebarOpen" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" x-show="sidebarOpen" />
                </svg>
            </button>
        </div>

        <!-- Perfil do Usuário -->
        <div class="p-4 border-b bg-gray-50" :class="{'text-center': !sidebarOpen}">
            <div class="flex items-center" :class="{'justify-center': !sidebarOpen, 'space-x-3': sidebarOpen}">
                <div class="flex-shrink-0">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}"
                             alt="Avatar"
                             class="h-10 w-10 rounded-full object-cover"
                             onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.png') }}';">
                    @else
                        <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0" x-show="sidebarOpen">
                    <div class="text-sm font-medium text-gray-900 truncate">
                        {{ auth()->user()->name }}
                    </div>
                    <a href="{{ route('profile.edit') }}" class="text-sm text-blue-600 hover:underline">
                        Editar Perfil
                    </a>
                </div>
            </div>
        </div>

        <!-- Menu de Navegação -->
        <nav class="mt-4 flex-1 overflow-y-auto">
            <ul>
                <!-- Dashboard -->
                @can('view dashboard')
                    <li class="relative" x-data="{ tooltip: false }" @mouseenter="tooltip = sidebarOpen ? false : true" @mouseleave="tooltip = false">
                        <a href="{{ auth()->user()->hasAnyRole(['secretary', 'education_secretary']) ? route('secretary.dashboard') : route('mayor.dashboard') }}"
                           class="block py-2 hover:bg-gray-100 {{ request()->routeIs('dashboard') || request()->routeIs('secretary.dashboard') ? 'bg-gray-100' : '' }} flex items-center"
                           :class="{'px-4': sidebarOpen, 'justify-center px-0': !sidebarOpen}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="{'mr-3': sidebarOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span x-show="sidebarOpen">Dashboard</span>
                        </a>
                        <div x-show="tooltip" class="absolute left-full top-0 ml-2 bg-black text-white text-sm px-2 py-1 rounded z-10">
                            Dashboard
                        </div>
                    </li>
                @endcan

                <!-- Tipos de Despesas -->
                @can('manage departments')
                    @if(!auth()->user()->hasRole('mayor'))
                        <li class="relative" x-data="{ tooltip: false }" @mouseenter="tooltip = sidebarOpen ? false : true" @mouseleave="tooltip = false">
                            <a href="{{ route('expense-types.index') }}"
                               class="block py-2 hover:bg-gray-100 {{ request()->routeIs('expense-types.*') ? 'bg-gray-100' : '' }} flex items-center"
                               :class="{'px-4': sidebarOpen, 'justify-center px-0': !sidebarOpen}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="{'mr-3': sidebarOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <span x-show="sidebarOpen">Tipos de Despesas</span>
                            </a>
                            <div x-show="tooltip" class="absolute left-full top-0 ml-2 bg-black text-white text-sm px-2 py-1 rounded z-10">
                                Tipos de Despesas
                            </div>
                        </li>
                    @endif
                @endcan

                <!-- Despesas -->
                @can('manage departments')
                    @if(!auth()->user()->hasRole('mayor'))
                        <li class="relative" x-data="{ tooltip: false }" @mouseenter="tooltip = sidebarOpen ? false : true" @mouseleave="tooltip = false">
                            <a href="{{ route('expenses.index') }}"
                               class="block py-2 hover:bg-gray-100 {{ request()->routeIs('expenses.*') ? 'bg-gray-100' : '' }} flex items-center"
                               :class="{'px-4': sidebarOpen, 'justify-center px-0': !sidebarOpen}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="{'mr-3': sidebarOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span x-show="sidebarOpen">Despesas</span>
                            </a>
                            <div x-show="tooltip" class="absolute left-full top-0 ml-2 bg-black text-white text-sm px-2 py-1 rounded z-10">
                                Despesas
                            </div>
                        </li>
                    @endif
                @endcan

                <!-- Análise Escolar -->
                @can('view all schools')
                    <li class="relative" x-data="{ tooltip: false }" @mouseenter="tooltip = sidebarOpen ? false : true" @mouseleave="tooltip = false">
                        <a href="{{ route('reports.students') }}"
                           class="block py-2 hover:bg-gray-100 {{ request()->routeIs('reports.students') ? 'bg-gray-100' : '' }} flex items-center"
                           :class="{'px-4': sidebarOpen, 'justify-center px-0': !sidebarOpen}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="{'mr-3': sidebarOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                            </svg>
                            <span x-show="sidebarOpen">Análise Escolar</span>
                        </a>
                        <div x-show="tooltip" class="absolute left-full top-0 ml-2 bg-black text-white text-sm px-2 py-1 rounded z-10">
                            Análise Escolar
                        </div>
                    </li>
                @endcan

                <!-- Relatório Cantina -->
                @can('view cantina report')
                    <li class="relative" x-data="{ tooltip: false }" @mouseenter="tooltip = sidebarOpen ? false : true" @mouseleave="tooltip = false">
                        <a href="{{ route('cantina.report') }}"
                           class="block py-2 hover:bg-gray-100 {{ request()->routeIs('cantina.report') ? 'bg-gray-100' : '' }} flex items-center"
                           :class="{'px-4': sidebarOpen, 'justify-center px-0': !sidebarOpen}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="{'mr-3': sidebarOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span x-show="sidebarOpen">Relatório Cantina</span>
                        </a>
                        <div x-show="tooltip" class="absolute left-full top-0 ml-2 bg-black text-white text-sm px-2 py-1 rounded z-10">
                            Relatório Cantina
                        </div>
                    </li>
                @endcan

                <!-- Cadastrar Alunos (apenas para sector_leader de escola) -->
                @if(auth()->user()->hasRole('sector_leader') && auth()->user()->department && auth()->user()->department->is_school)
                    @can('manage students')
                        <li class="relative" x-data="{ tooltip: false }" @mouseenter="tooltip = sidebarOpen ? false : true" @mouseleave="tooltip = false">
                            <a href="{{ route('enrollments.create') }}"
class="block py-2 hover:bg-gray-100 {{ request()->routeIs('enrollments.create') ? 'bg-gray-100' : '' }} flex items-center"
                               :class="{'px-4': sidebarOpen, 'justify-center px-0': !sidebarOpen}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="{'mr-3': sidebarOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                <span x-show="sidebarOpen">Cadastrar Alunos</span>
                            </a>
                            <div x-show="tooltip" class="absolute left-full top-0 ml-2 bg-black text-white text-sm px-2 py-1 rounded z-10">
                                Cadastrar Alunos
                            </div>
                        </li>
                    @endcan
                @endif

                @can('manage users')
                    @if(!auth()->user()->hasRole('mayor'))
                        <li class="relative" x-data="{ tooltip: false }" @mouseenter="tooltip = sidebarOpen ? false : true" @mouseleave="tooltip = false">
                            <a href="{{ route('secretary.sector-leaders') }}"
                               class="block py-2 hover:bg-gray-100 {{ request()->routeIs('secretary.sector-leaders') ? 'bg-gray-100' : '' }} flex items-center"
                               :class="{'px-4': sidebarOpen, 'justify-center px-0': !sidebarOpen}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="{'mr-3': sidebarOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span x-show="sidebarOpen">Associar Líderes</span>
                            </a>
                            <div x-show="tooltip" class="absolute left-full top-0 ml-2 bg-black text-white text-sm px-2 py-1 rounded z-10">
                                Associar Líderes
                            </div>
                        </li>
                    @endif
                @endcan

                <!-- Gerenciar Departamentos (não visível para mayor) -->
                @can('manage departments')
                    @if(!auth()->user()->hasRole('mayor'))
                        <li class="relative" x-data="{ tooltip: false }" @mouseenter="tooltip = sidebarOpen ? false : true" @mouseleave="tooltip = false">
                            <a href="{{ route('secretary.departments') }}"
                               class="block py-2 hover:bg-gray-100 {{ request()->routeIs('secretary.departments') ? 'bg-gray-100' : '' }} flex items-center"
                               :class="{'px-4': sidebarOpen, 'justify-center px-0': !sidebarOpen}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="{'mr-3': sidebarOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span x-show="sidebarOpen">Gerenciar Departamentos</span>
                            </a>
                            <div x-show="tooltip" class="absolute left-full top-0 ml-2 bg-black text-white text-sm px-2 py-1 rounded z-10">
                                Gerenciar Departamentos
                            </div>
                        </li>
                    @endif
                @endcan

                <!-- Credenciais Líderes -->
                @can('manage users')
                    <li class="relative" x-data="{ tooltip: false }" @mouseenter="tooltip = sidebarOpen ? false : true" @mouseleave="tooltip = false">
                        <a href="{{ route('credentials.index') }}"
                           class="block py-2 hover:bg-gray-100 {{ request()->routeIs('credentials.*') ? 'bg-gray-100' : '' }} flex items-center"
                           :class="{'px-4': sidebarOpen, 'justify-center px-0': !sidebarOpen}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="{'mr-3': sidebarOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            <span x-show="sidebarOpen">Credenciais Líderes</span>
                        </a>
                        <div x-show="tooltip" class="absolute left-full top-0 ml-2 bg-black text-white text-sm px-2 py-1 rounded z-10">
                            Credenciais Líderes
                        </div>
                    </li>
                @endcan

                <!-- Credenciais Secretários -->
                @can('manage secretaries')
                    <li class="relative" x-data="{ tooltip: false }" @mouseenter="tooltip = sidebarOpen ? false : true" @mouseleave="tooltip = false">
                        <a href="{{ route('credentials.index') }}"
                           class="block py-2 hover:bg-gray-100 {{ request()->routeIs('credentials.*') ? 'bg-gray-100' : '' }} flex items-center"
                           :class="{'px-4': sidebarOpen, 'justify-center px-0': !sidebarOpen}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="{'mr-3': sidebarOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                            </svg>
                            <span x-show="sidebarOpen">Credenciais Secretários</span>
                        </a>
                        <div x-show="tooltip" class="absolute left-full top-0 ml-2 bg-black text-white text-sm px-2 py-1 rounded z-10">
                            Credenciais Secretários
                        </div>
                    </li>
                @endcan

                <!-- Gerenciar Secretários -->
                @can('manage secretaries')
                    <li class="relative" x-data="{ tooltip: false }" @mouseenter="tooltip = sidebarOpen ? false : true" @mouseleave="tooltip = false">
                        <a href="{{ route('secretaries.manage') }}"
                           class="block py-2 hover:bg-gray-100 {{ request()->routeIs('secretaries.manage') ? 'bg-gray-100' : '' }} flex items-center"
                           :class="{'px-4': sidebarOpen, 'justify-center px-0': !sidebarOpen}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="{'mr-3': sidebarOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span x-show="sidebarOpen">Gerenciar Secretários</span>
                        </a>
                        <div x-show="tooltip" class="absolute left-full top-0 ml-2 bg-black text-white text-sm px-2 py-1 rounded z-10">
                            Gerenciar Secretários
                        </div>
                    </li>
                @endcan

                <!-- Teto de Gastos -->
                @can('view financial dashboard')
                    <li class="relative" x-data="{ tooltip: false }" @mouseenter="tooltip = sidebarOpen ? false : true" @mouseleave="tooltip = false">
                        <a href="{{ route('spending-caps.index') }}"
                           class="block py-2 hover:bg-gray-100 {{ request()->routeIs('spending-caps.*') ? 'bg-gray-100' : '' }} flex items-center"
                           :class="{'px-4': sidebarOpen, 'justify-center px-0': !sidebarOpen}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="{'mr-3': sidebarOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                            </svg>
                            <span x-show="sidebarOpen">Teto de Gastos</span>
                        </a>
                        <div x-show="tooltip" class="absolute left-full top-0 ml-2 bg-black text-white text-sm px-2 py-1 rounded z-10">
                            Teto de Gastos
                        </div>
                    </li>
                @endcan
            </ul>
        </nav>

        <!-- Footer com Logout -->
        <div class="border-t p-4" :class="{'text-center': !sidebarOpen}">
            <form action="{{ secure_url(route('logout', [], false)) }}" method="POST">
                @csrf
                <button type="submit" class="text-red-500 hover:bg-gray-100 py-2 rounded flex items-center" 
                        :class="{'px-4 w-full text-left': sidebarOpen, 'justify-center px-0 w-full': !sidebarOpen}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="{'mr-3': sidebarOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span x-show="sidebarOpen">Sair</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Menu Mobile (visível apenas em dispositivos móveis) -->
<div x-show="mobileMenuOpen" 
     class="fixed inset-0 z-40 bg-black bg-opacity-50" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="mobileMenuOpen = false"></div>
     
<div x-show="mobileMenuOpen" 
     class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out" 
     :class="{'translate-x-0': mobileMenuOpen, '-translate-x-full': !mobileMenuOpen}">
    
    <div class="p-4 border-b flex justify-between items-center">
        <h1 class="text-xl font-bold">AgilyCity</h1>
        <button @click="mobileMenuOpen = false" class="text-gray-500 hover:text-gray-700 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

        <!-- Perfil do Usuário Mobile -->
        <div class="p-4 border-b bg-gray-50">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}"
                             alt="Avatar"
                             class="h-10 w-10 rounded-full object-cover"
                             onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.png') }}';">
                    @else
                        <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
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

        <!-- Menu de Navegação Mobile -->
        <nav class="mt-4 flex-1 overflow-y-auto">
            <ul>
                <!-- Dashboard -->
                @can('view dashboard')
                    <li>
                        <a href="{{ auth()->user()->hasAnyRole(['secretary', 'education_secretary']) ? route('secretary.dashboard') : route('mayor.dashboard') }}"
                           class="block px-4 py-2 hover:bg-gray-100 {{ request()->routeIs('dashboard') || request()->routeIs('secretary.dashboard') ? 'bg-gray-100' : '' }} flex items-center"
                           @click="mobileMenuOpen = false">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                @endcan

                <!-- Tipos de Despesas -->
                @can('manage departments')
                    @if(!auth()->user()->hasRole('mayor'))
                        <li>
                            <a href="{{ route('expense-types.index') }}"
                               class="block px-4 py-2 hover:bg-gray-100 {{ request()->routeIs('expense-types.*') ? 'bg-gray-100' : '' }} flex items-center"
                               @click="mobileMenuOpen = false">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <span>Tipos de Despesas</span>
                            </a>
                        </li>
                    @endif
                @endcan

                <!-- Despesas -->
                @can('manage departments')
                    @if(!auth()->user()->hasRole('mayor'))
                        <li>
                            <a href="{{ route('expenses.index') }}"
                               class="block px-4 py-2 hover:bg-gray-100 {{ request()->routeIs('expenses.*') ? 'bg-gray-100' : '' }} flex items-center"
                               @click="mobileMenuOpen = false">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Despesas</span>
                            </a>
                        </li>
                    @endif
                @endcan

                <!-- Análise Escolar -->
                @can('view all schools')
                    <li>
                        <a href="{{ route('reports.students') }}"
                           class="block px-4 py-2 hover:bg-gray-100 {{ request()->routeIs('reports.students') ? 'bg-gray-100' : '' }} flex items-center"
                           @click="mobileMenuOpen = false">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998a12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                            </svg>
                            <span>Análise Escolar</span>
                        </a>
                    </li>
                @endcan

                <!-- Outros itens do menu mobile -->
                <!-- Adicione todos os outros itens do menu aqui, seguindo o mesmo padrão -->
                
                <!-- Continuar com outros itens... -->
                
                <!-- Credenciais Secretários -->
                @can('manage secretaries')
                    <li>
                        <a href="{{ route('credentials.index') }}"
                           class="block px-4 py-2 hover:bg-gray-100 {{ request()->routeIs('credentials.*') ? 'bg-gray-100' : '' }} flex items-center"
                           @click="mobileMenuOpen = false">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                            </svg>
                            <span>Credenciais Secretários</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </nav>

        <!-- Footer com Logout para Mobile -->
        <div class="border-t p-4 mt-auto">
            <form action="{{ secure_url(route('logout', [], false)) }}" method="POST">
                @csrf
                <button type="submit" class="text-red-500 hover:bg-gray-100 py-2 px-4 rounded flex items-center w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Sair</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Header Mobile (apenas para dispositivos móveis) -->
<!-- Header Mobile (apenas para dispositivos móveis) -->
<div x-show="window.innerWidth < 768" class="fixed top-0 left-0 w-full z-30 bg-white shadow-md">
    <div class="flex items-center justify-between p-4">
        <button @click="mobileMenuOpen = true" class="text-gray-500 hover:text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <span class="text-xl font-bold">AgilyCity</span>
        <div class="w-6 h-6 opacity-0"><!-- Espaço invisível --></div>
    </div>
</div>
    <!-- Conteúdo Principal -->
<div class="flex-1 overflow-y-auto" :class="{'pt-16 mt-6': window.innerWidth < 768}">
    <div class="p-6">
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
</div>

<!-- Scripts da Página -->
@stack('scripts')
</body>
</html>