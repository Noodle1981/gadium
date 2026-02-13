<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::where('name', '!=', 'Super Admin')->get();
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        // Proteger asignación de Super Admin
        if ($validated['role'] === 'Super Admin') {
            return redirect()->route('app.users.create')
                ->with('error', 'No se puede asignar el rol Super Admin.');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Asignar rol
        $user->assignRole($validated['role']);

        return redirect()->route('app.users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        // No se puede editar Super Admin
        if ($user->hasRole('Super Admin')) {
            return redirect()->route('app.users.index')
                ->with('error', 'Este usuario no puede ser editado.');
        }

        $roles = Role::where('name', '!=', 'Super Admin')->get();
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Proteger Super Admin
        if ($user->hasRole('Super Admin')) {
            return redirect()->route('app.users.index')
                ->with('error', 'Este usuario no puede ser editado.');
        }

        if ($request->input('role') === 'Super Admin') {
            return redirect()->route('app.users.edit', $user)
                ->with('error', 'No se puede asignar el rol Super Admin.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,name',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Sincronizar rol (elimina roles anteriores y asigna el nuevo)
        $user->syncRoles([$validated['role']]);

        return redirect()->route('app.users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage (Soft Delete).
     */
    public function destroy(User $user)
    {
        // Proteger contra eliminación del Super Admin
        if ($user->hasRole('Super Admin')) {
            return redirect()->route('app.users.index')
                ->with('error', 'No se puede eliminar al Super Administrador.');
        }

        $user->delete(); // Soft delete

        return redirect()->route('app.users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }
}
