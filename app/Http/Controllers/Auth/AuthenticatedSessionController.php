<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        // Obtém o usuário autenticado
        $user = Auth::user();

        // Verifica se é o primeiro acesso e marca com flag de sessão
        if ($user->first_access) {
            // Marca com flag de primeiro acesso na sessão
            session()->flash('first_access', true);
        }

        // Redirecionamento baseado no papel do usuário
        if ($user->hasAnyRole(['secretary', 'education_secretary'])) {
            return redirect()->intended(route('secretary.dashboard'));
        }

        if ($user->hasRole('mayor')) {
            return redirect()->intended(route('mayor.dashboard'));
        }

        // Padrão para outros usuários
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
