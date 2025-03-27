<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Routing\Controller;

class PermissionController extends Controller
{
    public function __construct()
    {
        // Aplicar middleware no construtor
        $this->middleware('auth');
        $this->middleware('can:manage users');
    }

    // Listar todas as permissões
    public function index()
    {
        $permissions = Permission::orderBy('name')->get();
        $roles = Role::with('permissions')->orderBy('name')->get();

        return view('admin.permissions.index', compact('permissions', 'roles'));
    }

    // Armazenar nova permissão
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name'
        ]);

        Permission::create($validated);

        return redirect()->route('permissions.index')
            ->with('success', 'Permissão criada com sucesso!');
    }

    // Atualizar permissões de um role
    public function updateRolePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $permissions = $request->input('permissions', []);
        $role->syncPermissions($permissions);

        return redirect()->route('permissions.index')
            ->with('success', 'Permissões do cargo atualizadas com sucesso!');
    }

    // Atribuir roles a um usuário
    public function assignRolesToUser(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ]);

        $roles = $request->input('roles', []);
        $user->syncRoles($roles);

        return redirect()->back()
            ->with('success', 'Cargos do usuário atualizados com sucesso!');
    }
}
