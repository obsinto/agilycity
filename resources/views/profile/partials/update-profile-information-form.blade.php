<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Informações do Perfil') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __("Atualize suas informações de perfil e endereço de email.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="flex items-center gap-4">
            <div class="relative">
                @if(auth()->user()->avatar)
                    <img id="avatar-preview"
                         src="{{ Storage::url(auth()->user()->avatar) }}"
                         class="w-20 h-20 rounded-full object-cover"
                         onerror="this.onerror=null; this.parentElement.innerHTML = '<div class=\'w-20 h-20 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-xl\'>' + '{{ substr(auth()->user()->name, 0, 1) }}' + '</div>';">
                @else
                    <div id="avatar-preview"
                         class="w-20 h-20 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-xl">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                @endif

                <label for="avatar"
                       class="absolute bottom-0 right-0 bg-white rounded-full p-1 shadow cursor-pointer hover:bg-gray-100">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </label>
                <input type="file"
                       id="avatar"
                       name="avatar"
                       class="hidden"
                       accept="image/*"
                       onchange="previewImage(this)">
            </div>
            @if(auth()->user()->avatar)
                <button type="button" onclick="removeAvatar()" class="text-sm text-red-600 hover:text-red-800">Remover
                    Foto
                </button>
            @endif
        </div>

        <div>
            <x-input-label for="name" :value="__('Nome')"/>
            <x-text-input id="name"
                          name="name"
                          type="text"
                          class="mt-1 block w-full"
                          :value="old('name', $user->name)"
                          required
                          autofocus
                          autocomplete="name"/>
            <x-input-error class="mt-2" :messages="$errors->get('name')"/>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')"/>
            <x-text-input id="email"
                          name="email"
                          type="email"
                          class="mt-1 block w-full"
                          :value="old('email', $user->email)"
                          required
                          autocomplete="username"/>
            <x-input-error class="mt-2" :messages="$errors->get('email')"/>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Seu endereço de email não está verificado.') }}

                        <button form="send-verification"
                                class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Clique aqui para reenviar o email de verificação.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('Um novo link de verificação foi enviado para seu endereço de email.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Salvar') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Salvo.') }}</p>
            @endif
        </div>
    </form>

</section>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            var preview = document.getElementById('avatar-preview');

            reader.onload = function (e) {
                var img = document.createElement('img');
                img.id = 'avatar-preview';
                img.className = 'w-20 h-20 rounded-full object-cover';
                img.src = e.target.result;
                img.onerror = function () {
                    this.onerror = null;
                    var div = document.createElement('div');
                    div.id = 'avatar-preview';
                    div.className = 'w-20 h-20 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-xl';
                    div.textContent = '{{ substr(auth()->user()->name, 0, 1) }}';
                    this.parentElement.replaceChild(div, this);
                };
                preview.parentElement.replaceChild(img, preview);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeAvatar() {
        if (confirm('Tem certeza que deseja remover sua foto de perfil?')) {
            fetch('{{ route("profile.remove.avatar") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro ao remover a foto de perfil.');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Atualiza a visualização do avatar
                        const avatarPreview = document.getElementById('avatar-preview');
                        avatarPreview.innerHTML = `
                    <div class="w-20 h-20 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-xl">
                        {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
`;

                        // Remove o botão "Remover Foto"
                        const removeButton = document.querySelector('button[onclick="removeAvatar()"]');
                        if (removeButton) {
                            removeButton.remove();
                        }
                    } else {
                        throw new Error('Erro ao remover a foto de perfil.');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Ocorreu um erro ao remover a foto de perfil. Tente novamente.');
                });
        }
    }
</script>
