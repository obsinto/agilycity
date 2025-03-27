@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h4>Administração de Permissões</h4>
                            <button class="btn btn-primary" data-toggle="modal" data-target="#newPermissionModal">
                                Nova Permissão
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <h5>Permissões Disponíveis</h5>
                                <table class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Ações</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($permissions as $permission)
                                        <tr>
                                            <td>{{ $permission->name }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-danger"
                                                        onclick="confirmDelete('{{ $permission->id }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <h5>Cargos e suas Permissões</h5>
                                @foreach($roles as $role)
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <strong>{{ $role->name }}</strong>
                                        </div>
                                        <div class="card-body">
                                            <form action="{{ route('permissions.update-role', $role) }}" method="POST">
                                                @csrf
                                                <div class="row">
                                                    @foreach($permissions as $permission)
                                                        <div class="col-md-6 mb-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                       name="permissions[]"
                                                                       value="{{ $permission->id }}"
                                                                       id="perm_{{ $role->id }}_{{ $permission->id }}"
                                                                    {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                                <label class="form-check-label"
                                                                       for="perm_{{ $role->id }}_{{ $permission->id }}">
                                                                    {{ $permission->name }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <button type="submit" class="btn btn-sm btn-primary mt-2">
                                                    Salvar Permissões
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para nova permissão -->
    <div class="modal fade" id="newPermissionModal" tabindex="-1" role="dialog"
         aria-labelledby="newPermissionModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newPermissionModalLabel">Criar Nova Permissão</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('permissions.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Nome da Permissão</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <small class="form-text text-muted">Exemplo: manage_users, view_reports</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Criar Permissão</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script para confirmação de exclusão -->
    <script>
        function confirmDelete(permissionId) {
            if (confirm('Tem certeza que deseja excluir esta permissão? Esta ação não pode ser desfeita.')) {
                fetch(`/permissions/${permissionId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                }).then(response => {
                    if (response.ok) {
                        window.location.reload();
                    }
                });
            }
        }
    </script>
@endsection
