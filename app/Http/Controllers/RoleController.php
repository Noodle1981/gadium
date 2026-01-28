<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::withCount('users');
        
        // Managers no pueden ver el rol Super Admin
        if (auth()->user()->hasRole('Manager') && !auth()->user()->hasRole('Super Admin')) {
            $roles->where('name', '!=', 'Super Admin');
        }
        
        $roles = $roles->get();
        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
        ]);

        return redirect()->route('app.roles.permissions', $role)
            ->with('success', 'Rol creado exitosamente. Ahora asigne los permisos.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $role->load('permissions', 'users');
        return view('roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        // Proteger Super Admin
        if ($role->name === 'Super Admin') {
            return redirect()->route('app.roles.index')
                ->with('error', 'El rol Super Admin no puede ser editado.');
        }

        return view('roles.edit', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        // Proteger Super Admin
        if ($role->name === 'Super Admin') {
            return redirect()->route('app.roles.index')
                ->with('error', 'El rol Super Admin no puede ser modificado.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:500',
        ]);

        $role->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('app.roles.index')
            ->with('success', 'Rol actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Proteger Super Admin
        if ($role->name === 'Super Admin') {
            return redirect()->route('app.roles.index')
                ->with('error', 'El rol Super Admin no puede ser eliminado.');
        }

        // Verificar si tiene usuarios asignados
        if ($role->users()->count() > 0) {
            return redirect()->route('app.roles.index')
                ->with('error', 'No se puede eliminar un rol con usuarios asignados.');
        }

        $role->delete();

        return redirect()->route('app.roles.index')
            ->with('success', 'Rol eliminado exitosamente.');
    }

    /**
     * Show the permissions assignment form.
     */
    public function permissions(Role $role)
    {
        $role->load('permissions');
        
        // Agrupar permisos por módulo
        $modules = [
            'Usuarios' => Permission::where('name', 'like', '%_users')->get(),
            'Roles' => Permission::where('name', 'like', '%_roles')->get(),
            'Ventas' => Permission::where('name', 'like', '%_sales')->get(),
            'Producción' => Permission::where('name', 'like', '%_production')->get(),
            'RRHH' => Permission::where('name', 'like', '%_hr')->get(),
            'Dashboards' => Permission::where('name', 'like', '%_dashboards')->get(),
        ];

        return view('roles.permissions', compact('role', 'modules'));
    }

    /**
     * Update the permissions for a role.
     */
    public function updatePermissions(Request $request, Role $role)
    {
        // Proteger Super Admin
        if ($role->name === 'Super Admin') {
            return redirect()->route('app.roles.index')
                ->with('error', 'Los permisos del Super Admin no pueden ser modificados.');
        }

        $permissions = $request->input('permissions', []);
        
        // Sincronizar permisos
        $role->syncPermissions($permissions);

        return redirect()->route('app.roles.index')
            ->with('success', 'Permisos actualizados exitosamente.');
    }
}
