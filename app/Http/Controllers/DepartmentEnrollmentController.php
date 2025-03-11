<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller responsável por criar, editar e remover matrículas (quantidade de alunos)
 * em um departamento que seja escola.
 */
class DepartmentEnrollmentController extends Controller
{
    /**
     * Exibe o formulário de cadastro de alunos e a listagem (histórico) do próprio departamento.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user();
        $department = $user->department;

        // Verifica se o departamento existe e se é uma escola
        if (!$department || !$department->is_school) {
            return redirect()->route('dashboard')
                ->with('error', 'Seu departamento não é uma escola. Acesso negado.');
        }

        // Busca o "histórico" de matrículas desse departamento, do mais recente para o mais antigo
        $enrollments = DepartmentEnrollment::where('department_id', $department->id)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        return view('enrollments.create', compact('department', 'enrollments'));
    }

    /**
     * Cria ou atualiza (via upsert) um registro de matrícula baseado em (year, month) do dept atual.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $department = $user->department;

        if (!$department || !$department->is_school) {
            return redirect()->route('dashboard')
                ->with('error', 'Seu departamento não é uma escola. Acesso negado.');
        }

        // Validação
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'students_count' => 'required|integer|min:0',
        ]);

        // Upsert do registro (se existir year+month, atualiza; senão, cria)
        DepartmentEnrollment::updateOrCreate(
            [
                'department_id' => $department->id,
                'year' => $validated['year'],
                'month' => $validated['month'],
            ],
            [
                'students_count' => $validated['students_count'],
            ]
        );

        return redirect()->route('enrollments.create')
            ->with('success', 'Quantidade de alunos cadastrada/atualizada com sucesso!');
    }

    /**
     * Exibe o formulário de edição de um registro específico de matrícula.
     *
     * @param \App\Models\DepartmentEnrollment $enrollment
     * @return \Illuminate\Http\Response
     */
    public function edit(DepartmentEnrollment $enrollment)
    {
        $user = Auth::user();

        // Verifica se a matrícula pertence de fato ao departamento do usuário
        if (!$user->department || $user->department->id !== $enrollment->department_id) {
            return redirect()->route('dashboard')
                ->with('error', 'Você não tem permissão para editar este registro.');
        }

        // Verifica se o departamento é mesmo uma escola
        $department = $user->department;
        if (!$department->is_school) {
            return redirect()->route('dashboard')
                ->with('error', 'Seu departamento não é uma escola. Acesso negado.');
        }

        return view('enrollments.edit', compact('enrollment', 'department'));
    }

    /**
     * Atualiza um registro específico de matrícula (year, month, students_count).
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\DepartmentEnrollment $enrollment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DepartmentEnrollment $enrollment)
    {
        $user = Auth::user();
        // Garante que esse registro pertença ao dept do user
        if (!$user->department || $user->department->id !== $enrollment->department_id) {
            return redirect()->route('dashboard')
                ->with('error', 'Você não tem permissão para editar este registro.');
        }

        // Validação
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'students_count' => 'required|integer|min:0',
        ]);

        // Atualiza o registro
        $enrollment->update($validated);

        return redirect()->route('enrollments.create')
            ->with('success', 'Registro de alunos atualizado com sucesso!');
    }

    /**
     * Exclui um registro de matrícula.
     *
     * @param \App\Models\DepartmentEnrollment $enrollment
     * @return \Illuminate\Http\Response
     */
    public function destroy(DepartmentEnrollment $enrollment)
    {
        $user = Auth::user();

        // Verifica se pertence ao dept do user
        if (!$user->department || $user->department->id !== $enrollment->department_id) {
            return redirect()->route('dashboard')
                ->with('error', 'Você não tem permissão para excluir este registro.');
        }

        $enrollment->delete();

        return redirect()->route('enrollments.create')
            ->with('success', 'Registro de alunos deletado com sucesso!');
    }
}
