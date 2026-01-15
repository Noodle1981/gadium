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
        // Managers no pueden ver Super Admins
        $query = User::query();
        
        if (auth()->user()->hasRole('Manager') && !auth()->user()->hasRole('Super Admin')) {
            $query->whereDoesntHave('roles', function($q) {
                $q->where('name', 'Super Admin');
            });
        }
        
        return view('users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Managers no pueden asignar rol Super Admin
        $roles = Role::query();
        
        if (auth()->user()->hasRole('Manager') && !auth()->user()->hasRole('Super Admin')) {
            $roles->where('name', '!=', 'Super Admin');
        }
        
        $roles = $roles->get();
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
            'role' => 'required|exists:roles,name',
        ]);

        // Managers no pueden asignar rol Super Admin
        if (auth()->user()->hasRole('Manager') && !auth()->user()->hasRole('Super Admin')) {
            if ($validated['role'] === 'Super Admin') {
                return redirect()->route('users.create')
                    ->with('error', 'No tiene permisos para asignar el rol Super Admin.');
            }
        }

        // Crear usuario sin contraseña (la configurará después)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(str()->random(32)), // Contraseña temporal aleatoria
        ]);

        // Asignar rol
        $user->assignRole($validated['role']);

        // Enviar email de invitación
        $user->notify(new \App\Notifications\UserInvitation());
        
        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente. Se ha enviado un email de invitación a ' . $user->email);
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
        // Managers no pueden editar Super Admins
        if (auth()->user()->hasRole('Manager') && !auth()->user()->hasRole('Super Admin')) {
            if ($user->hasRole('Super Admin')) {
                return redirect()->route('users.index')
                    ->with('error', 'No tiene permisos para editar un Super Admin.');
            }
        }
        
        // Managers no pueden asignar rol Super Admin
        $roles = Role::query();
        
        if (auth()->user()->hasRole('Manager') && !auth()->user()->hasRole('Super Admin')) {
            $roles->where('name', '!=', 'Super Admin');
        }
        
        $roles = $roles->get();
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Managers no pueden editar Super Admins
        if (auth()->user()->hasRole('Manager') && !auth()->user()->hasRole('Super Admin')) {
            if ($user->hasRole('Super Admin')) {
                return redirect()->route('users.index')
                    ->with('error', 'No tiene permisos para editar un Super Admin.');
            }
            
            // Managers no pueden asignar rol Super Admin
            if ($request->input('role') === 'Super Admin') {
                return redirect()->route('users.edit', $user)
                    ->with('error', 'No tiene permisos para asignar el rol Super Admin.');
            }
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

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage (Soft Delete).
     */
    public function destroy(User $user)
    {
        // Proteger contra eliminación del Super Admin
        if ($user->hasRole('Super Admin')) {
            return redirect()->route('users.index')
                ->with('error', 'No se puede eliminar al Super Administrador.');
        }

        $user->delete(); // Soft delete

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }
}
