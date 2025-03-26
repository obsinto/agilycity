{{-- resources/views/profile/partials/update-password-form.blade.php --}}
<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Atualizar Senha
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Certifique-se de que sua conta esteja usando uma senha longa e aleatória para manter a segurança.
        </p>
    </header>

    <!-- Notificações de sucesso e erro -->
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <label for="current_password" class="block text-sm font-medium text-gray-700">Senha Atual</label>
            <input id="current_password"
                   name="current_password"
                   type="password"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 transition-colors"
                   autocomplete="current-password"/>
            @error('current_password')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Nova Senha</label>
            <input id="password"
                   name="password"
                   type="password"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 transition-colors"
                   autocomplete="new-password"
                   required/>
            <p class="mt-1 text-xs text-gray-500">Mínimo de 8 caracteres</p>
            @error('password')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Nova
                Senha</label>
            <input id="password_confirmation"
                   name="password_confirmation"
                   type="password"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 transition-colors"
                   autocomplete="new-password"
                   required/>
            @error('password_confirmation')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Campo escondido para sinalizar que esta é uma atualização apenas de senha -->
        <input type="hidden" name="password_update_only" value="1">

        <div class="flex items-center gap-4">
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                Atualizar Senha
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >Senha atualizada com sucesso.</p>
            @endif
        </div>
    </form>
</section>
