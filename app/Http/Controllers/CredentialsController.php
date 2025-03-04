<?php


// app/Http/Controllers/CredentialsController.php
namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Models\User;

class CredentialsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('mayor')) {
            $users = User::role('secretary')->get(['name', 'email']);
            $title = 'Credenciais dos Secretários';
        } elseif ($user->hasRole('secretary')) {
            $users = User::where('secretary_id', $user->secretary_id)
                ->role('sector_leader')
                ->get(['name', 'email']);
            $title = 'Credenciais dos Líderes de Setor';
        } else {
            abort(403);
        }

        return view('credentials.index', compact('users', 'title'));
    }
}
