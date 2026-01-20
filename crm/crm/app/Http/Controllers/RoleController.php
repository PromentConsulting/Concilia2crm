<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return view('roles.index', [
            'roles' => $roles,
        ]);
    }

    public function create(): View
    {
        $permissions = Permission::query()
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        return view('roles.create', [
            'permissionsByModule' => $permissions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_default'  => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        if (! empty($data['is_default'])) {
            Role::query()->update(['is_default' => false]);
        }

        $role = Role::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_default'  => ! empty($data['is_default']),
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()
            ->route('roles.index')
            ->with('status', 'Rol creado correctamente.');
    }

    public function edit(Role $role): View
    {
        $role->load('permissions');

        $permissions = Permission::query()
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        return view('roles.edit', [
            'role'               => $role,
            'permissionsByModule'=> $permissions,
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_default'  => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        if (! empty($data['is_default'])) {
            Role::query()->where('id', '!=', $role->id)->update(['is_default' => false]);
        }

        $role->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_default'  => ! empty($data['is_default']),
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()
            ->route('roles.index')
            ->with('status', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->users()->exists()) {
            return redirect()
                ->route('roles.index')
                ->with('status', 'No se puede eliminar un rol con usuarios asignados.');
        }

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('status', 'Rol eliminado.');
    }
}
