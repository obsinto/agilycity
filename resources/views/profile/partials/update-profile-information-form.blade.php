{{-- resources/views/profile/partials/update-profile-information-form.blade.php --}}
<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Informações do Perfil
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Atualize as informações do seu perfil, senha e endereço de e-mail.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

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

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <!-- Avatar -->
        <div>
            <label for="avatar" class="block text-sm font-medium text-gray-700">Avatar</label>
            <div class="mt-1 flex items-center space-x-3">
                <div class="flex-shrink-0">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}"
                             alt="Avatar"
                             class="h-16 w-16 rounded-full object-cover"
                             id="avatar-preview">
                    @else
                        <div class="h-16 w-16 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-lg"
                             id="avatar-placeholder">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <img src="" alt="Avatar" class="h-16 w-16 rounded-full object-cover hidden" id="avatar-preview">
                    @endif
                </div>
                <div class="flex flex-col space-y-2">
                    <input type="file"
                           name="avatar"
                           id="avatar"
                           class="sr-only"
                           accept="image/*"
                           onchange="previewImage()">
                    <label for="avatar"
                           class="cursor-pointer py-1 px-3 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Alterar
                    </label>
                    @if(auth()->user()->avatar)
                        <button type="button"
                                class="py-1 px-3 border border-gray-300 rounded-md text-sm font-medium text-red-600 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500"
                                onclick="removeAvatar()">
                            Remover
                        </button>
                    @endif
                </div>
            </div>
            @error('avatar')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nome</label>
            <input id="name"
                   name="name"
                   type="text"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 transition-colors"
                   value="{{ old('name', $user->name) }}"
                   required
                   autofocus/>
            @error('name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- CPF -->
        <div>
            <label for="cpf" class="block text-sm font-medium text-gray-700">CPF</label>
            <input id="cpf"
                   name="cpf"
                   type="text"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 transition-colors"
                   value="{{ old('cpf', $user->cpf) }}"
                   required
                   maxlength="14"
                   oninput="this.value = formatCPF(this.value)"/>
            <p class="mt-1 text-xs text-gray-500">Formato: 000.000.000-00</p>
            @error('cpf')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input id="email"
                   name="email"
                   type="email"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 transition-colors"
                   value="{{ old('email', $user->email) }}"
                   required/>
            @error('email')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        Seu endereço de e-mail não foi verificado.

                        <button form="send-verification"
                                class="underline text-sm text-blue-600 hover:text-blue-800 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Clique aqui para reenviar o e-mail de verificação.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            Um novo link de verificação foi enviado para o seu endereço de e-mail.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Nova Senha</label>
            <input id="password"
                   name="password"
                   type="password"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 transition-colors"
                   placeholder="Deixe em branco para manter a senha atual"/>
            <p class="mt-1 text-xs text-gray-500">Mínimo de 8 caracteres</p>
            @error('password')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Confirmation -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Nova
                Senha</label>
            <input id="password_confirmation"
                   name="password_confirmation"
                   type="password"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 transition-colors"
                   placeholder="Confirme a nova senha"/>
            @error('password_confirmation')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-4">
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                Salvar
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >Salvo com sucesso.</p>
            @endif
        </div>
    </form>
</section>

<script>
    function formatCPF(cpf) {
        // Remove todos os caracteres não numéricos
        cpf = cpf.replace(/\D/g, '');

        // Aplica a formatação do CPF (000.000.000-00)
        if (cpf.length <= 11) {
            cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2");
            cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2");
            cpf = cpf.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        } else {
            // Limita a 11 dígitos
            cpf = cpf.slice(0, 11);
            cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2");
            cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2");
            cpf = cpf.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        }

        return cpf;
    }

    // Formatar CPF inicial
    window.addEventListener('DOMContentLoaded', function () {
        const cpfInput = document.getElementById('cpf');
        if (cpfInput && cpfInput.value) {
            cpfInput.value = formatCPF(cpfInput.value);
        }
    });

    function previewImage() {
        const input = document.getElementById('avatar');
        const preview = document.getElementById('avatar-preview');
        const placeholder = document.getElementById('avatar-placeholder');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if (placeholder) {
                    placeholder.classList.add('hidden');
                }
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeAvatar() {
        if (confirm('Tem certeza que deseja remover o avatar?')) {
            fetch('{{ route('profile.remove-avatar') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({})
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const preview = document.getElementById('avatar-preview');
                        const placeholder = document.getElementById('avatar-placeholder');

                        if (!placeholder) {
                            // Cria o placeholder se não existir
                            const newPlaceholder = document.createElement('div');
                            newPlaceholder.id = 'avatar-placeholder';
                            newPlaceholder.className = 'h-16 w-16 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-lg';
                            newPlaceholder.innerText = '{{ substr(auth()->user()->name, 0, 1) }}';

                            preview.parentNode.insertBefore(newPlaceholder, preview);
                        } else {
                            placeholder.classList.remove('hidden');
                        }

                        preview.classList.add('hidden');

                        // Recarrega a página para atualizar o estado
                        window.location.reload();
                    }
                });
        }
    }
</script>
