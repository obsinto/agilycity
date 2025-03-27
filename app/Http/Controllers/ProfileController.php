<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            // Log dos dados recebidos para fins de debugging
            Log::info('Dados do formulário: ', $request->all());

            // Atualiza os dados básicos do usuário
            $user = $request->user();

            // Garantir que o CPF seja incluído explicitamente
            $userData = $request->validated();

            // Preenche com os dados validados
            $user->name = $userData['name'];
            $user->email = $userData['email'];
            $user->cpf = $userData['cpf']; // Atribuição direta do CPF

            // Log para verificar se o CPF foi atribuído
            Log::info('CPF atribuído ao modelo: ' . $user->cpf);

            // Atualiza a senha se fornecida
            if (isset($userData['password']) && !empty($userData['password'])) {
                $user->password = Hash::make($userData['password']);
                $message = 'Perfil e senha atualizados com sucesso!';
            } else {
                $message = 'Perfil atualizado com sucesso!';
            }

            // Se o primeiro acesso for informado, atualiza
            if ($request->has('first_access')) {
                $user->first_access = false;
            }

            // Gerencia o upload do avatar
            if ($request->hasFile('avatar')) {
                if ($user->avatar) {
                    Storage::delete('public/' . $user->avatar);
                }

                $path = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $path;
            }

            // Marca email para verificação se foi alterado
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            // Log antes de salvar
            Log::info('Modelo User antes de salvar: ', ['cpf' => $user->cpf]);

            $user->save();

            // Log para verificar se o CPF foi salvo
            Log::info('User após salvar: ', ['cpf' => $user->cpf]);

            $status = 'success';
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar perfil: ' . $e->getMessage(), ['exception' => $e]);
            $message = 'Erro ao atualizar perfil: ' . $e->getMessage();
            $status = 'error';
        }

        // Se foi um primeiro acesso, redireciona para o dashboard apropriado
        if ($request->has('first_access')) {
            if ($user->hasAnyRole(['secretary', 'education_secretary'])) {
                return redirect()->route('secretary.dashboard')->with($status, $message);
            } elseif ($user->hasRole('mayor')) {
                return redirect()->route('mayor.dashboard')->with($status, $message);
            } elseif ($user->hasAnyRole(['sector_leader', 'school_leader', 'cantina_leader']) || $user->department_id !== null) {
                return redirect()->route('sector.dashboard')->with($status, $message);
            } else {
                // Fallback para qualquer outro tipo de usuário
                return redirect()->route('dashboard')->with($status, $message);
            }
        }

        // Se for atualização apenas de senha
        if ($request->has('password_update_only')) {
            return Redirect::route('profile.edit')->with($status, 'Senha atualizada com sucesso!');
        }

        return Redirect::route('profile.edit')->with($status, $message);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Remove o avatar do usuário.
     */
    public function removeAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->avatar) {
            // Remove o arquivo de avatar do storage
            Storage::delete('public/' . $user->avatar);

            // Define o avatar como null no banco de dados
            $user->avatar = null;
            $user->save();
        }

        return response()->json(['success' => true]);
    }
}
