<?php

namespace App\Http\Controllers;

use App\Models\Secretary;
use App\Models\User;
use Illuminate\Http\Request;

class SecretaryManagementController extends Controller
{
    public function index()
    {
        // Busca todos os usuários sem secretaria
        $availableUsers = User::whereNull('secretary_id')->get();

        // Busca todas as secretarias
        $secretaries = Secretary::with('secretary')->get();

        return view('secretaries.manage', compact('availableUsers', 'secretaries'));
    }

    public function associate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'secretary_id' => 'required|exists:secretaries,id'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->secretary_id = $request->secretary_id;
        $user->assignRole('secretary');
        $user->save();

        return redirect()->route('secretaries.manage')
            ->with('success', 'Secretário associado com sucesso!');
    }

    public function removeAssociation($userId)
    {
        $user = User::findOrFail($userId);
        $user->secretary_id = null;
        $user->removeRole('secretary');
        $user->save();

        return redirect()->route('secretaries.manage')
            ->with('success', 'Associação removida com sucesso!');
    }
}
