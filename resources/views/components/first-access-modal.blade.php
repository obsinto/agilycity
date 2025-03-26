<!-- resources/views/components/first-access-modal.blade.php -->
<div id="first-access-modal"
     x-data="{ show: true }"
     x-show="show"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="background-color: rgba(0,0,0,0.5);">
    <div class="flex items-center justify-center min-h-screen p-4">
        <!-- Removido x-on:click.away -->
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
            <div class="bg-gray-100 p-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    Atualização de Dados
                </h3>
            </div>

            <div class="p-6">
                <p class="text-gray-700 mb-4">
                    Bem-vindo ao sistema! Como este é seu primeiro acesso, por favor atualize suas informações abaixo:
                </p>

                <form method="POST" action="{{ route('profile.update') }}" @submit="show = false">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-4">
                        <!-- Nome -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                            <input type="text" id="name" name="name" value="{{ auth()->user()->name }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- E-mail -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                            <input type="email" id="email" name="email" value="{{ auth()->user()->email }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- CPF -->
                        <div>
                            <label for="cpf" class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
                            <input type="text" id="cpf" name="cpf" value="{{ auth()->user()->cpf }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   oninput="this.value = formatCPF(this.value)"
                                   maxlength="14">
                            @error('cpf')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nova Senha -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nova
                                Senha</label>
                            <input type="password" id="password" name="password" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirmar Senha -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar
                                Senha</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('password_confirmation')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <input type="hidden" name="first_access" value="0">
                    </div>

                    <div class="mt-6">
                        <button type="submit"
                                class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Atualizar Dados
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function formatCPF(cpf) {
        cpf = cpf.replace(/\D/g, '');
        if (cpf.length <= 11) {
            cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2");
            cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2");
            cpf = cpf.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        } else {
            cpf = cpf.slice(0, 11);
            cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2");
            cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2");
            cpf = cpf.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        }
        return cpf;
    }

    // Impede fechamento do modal
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('first-access-modal');
        if (modal) {
            // Bloqueia tecla Esc
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, true);

            // Impede cliques no fundo de fecharem o modal
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    e.stopPropagation();
                    e.preventDefault();
                }
            });
        }
    });
</script>
