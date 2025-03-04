<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SectorLeaderController extends Controller
{
    public function index()
    {
        // Verifica se o usuário é secretário
        $user = auth()->user();
        if (!$user->hasRole('secretary')) {
            abort(403, 'Acesso não autorizado');
        }

        // Busca os departamentos da secretaria do usuário
        $secretary = $user->secretary;
        $departments = $secretary->departments;

        // Busca líderes de setor atuais
        $currentSectorLeaders = User::role('sector_leader')
            ->whereHas('department', function ($query) use ($secretary) {
                $query->where('secretary_id', $secretary->id);
            })
            ->get();

        // Busca usuários disponíveis para serem líderes de setor
        $availableUsers = User::whereNull('department_id')
            ->doesntHave('department')
            ->get();

        return view('sector-leaders.index', compact('departments', 'currentSectorLeaders', 'availableUsers'));
    }

    public function store(Request $request)
    {
        // Verifica se o usuário é secretário
        $user = auth()->user();
        if (!$user->hasRole('secretary')) {
            abort(403, 'Acesso não autorizado');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'department_id' => 'required|exists:departments,id'
        ]);

        // Criar novo usuário
        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('password'), // Senha temporária
            'department_id' => $request->department_id,
            'secretary_id' => $user->secretary->id
        ]);

        // Atribuir role de líder de setor
        $newUser->assignRole('sector_leader');

        return redirect()->route('sector-leaders.index')
            ->with('success', 'Líder de setor cadastrado com sucesso!');
    }

    public function destroy($userId)
    {
        // Verifica se o usuário é secretário
        $user = auth()->user();
        if (!$user->hasRole('secretary')) {
            abort(403, 'Acesso não autorizado');
        }

        $sectorLeader = User::findOrFail($userId);
        $sectorLeader->department_id = null;
        $sectorLeader->removeRole('sector_leader');
        $sectorLeader->save();

        return redirect()->route('sector-leaders.index')
            ->with('success', 'Líder de setor removido com sucesso!');
    }
}
