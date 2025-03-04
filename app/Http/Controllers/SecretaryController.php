<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class SecretaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $secretary = auth()->user()->secretary;
        $departments = Department::where('secretary_id', $secretary->id)
            ->with('sectorLeaders')
            ->get();

        return view('dashboard.secretary', compact('departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // SecretaryController.php
    public function sectorLeaderAssignment()
    {
        $secretary = auth()->user()->secretary;

        $availableLeaders = User::whereNull('department_id')
            ->where('secretary_id', $secretary->id)
            ->role('sector_leader')
            ->get();

        $departments = Department::where('secretary_id', $secretary->id)
            ->with('sectorLeaders')
            ->get();

        return view('secretary.sector-leaders', compact('departments', 'availableLeaders'));
    }

    public function assignLeader(Request $request)
    {
        $request->validate([
            'leader_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id'
        ]);

        $user = User::findOrFail($request->leader_id);
        $user->department_id = $request->department_id;
        $user->save();

        return redirect()->back()->with('success', 'Líder associado com sucesso!');
    }

    public function removeLeader($id)
    {
        $user = User::findOrFail($id);

        if (auth()->user()->secretary_id !== $user->secretary_id) {
            abort(403);
        }

        $user->department_id = null;
        $user->save();

        return redirect()->back()->with('success', 'Líder removido com sucesso!');
    }

    // SecretaryController - adicione estes métodos
    public function departments()
    {
        $secretary = auth()->user()->secretary;
        $departments = Department::where('secretary_id', $secretary->id)->get();

        return view('secretary.departments', compact('departments'));
    }

    public function storeDepartment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        Department::create([
            'name' => $request->name,
            'description' => $request->description,
            'secretary_id' => auth()->user()->secretary_id
        ]);

        return redirect()->back()->with('success', 'Departamento criado com sucesso!');
    }

    public function deleteDepartment($id)
    {
        $department = Department::findOrFail($id);

        if ($department->secretary_id !== auth()->user()->secretary_id) {
            abort(403);
        }

        $department->delete();
        return redirect()->back()->with('success', 'Departamento removido com sucesso!');
    }
}
