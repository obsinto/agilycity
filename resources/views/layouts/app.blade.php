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

    @if(session('first_access'))
        @include('components.first-access-modal')
    @endif
</head>
<body class="bg-gray-100 h-screen">
<div x-data="{ sidebarOpen: window.innerWidth >= 768, mobileMenuOpen: false }"
     @resize.window="sidebarOpen = window.innerWidth >= 768 ? sidebarOpen : false; if (window.innerWidth >= 768) mobileMenuOpen = false;"
     class="flex h-screen bg-gray-100">

    <!-- Incluir o componente de navegação -->
    @include('layouts.navigation')

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
