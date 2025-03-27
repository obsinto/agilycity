@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Editar Cargos do Usuário: {{ $user->name }}</div>

                    <div class="card-body">
                        <form action="{{ route('permissions.assign-roles', $user) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Cargos</label>
                                @foreach($roles as $role)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="roles[]"
                                               value="{{ $role->id }}"
                                               id="role_{{ $role->id }}"
                                            {{ $user->hasRole($role->name) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="role_{{ $role->id }}">
                                            {{ $role->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <button type="submit" class="btn btn-primary">Salvar Cargos</button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
