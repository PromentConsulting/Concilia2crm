<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with('role')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('usuarios.index', [
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        $roles = Role::query()->orderBy('name')->get();

        return view('usuarios.create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id'  => ['nullable', 'integer', 'exists:roles,id'],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id'  => $data['role_id'] ?? null,
            'is_admin' => ! empty($data['is_admin']),
        ]);

        return redirect()
            ->route('usuarios.edit', $user)
            ->with('status', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario): View
    {
        $roles = Role::query()->orderBy('name')->get();

        $permissions = Permission::query()
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        $usuario->load(['role.permissions', 'permissionOverrides']);

        // mapa: permiso_id => override (null, true, false)
        $overrides = [];
        foreach ($usuario->permissionOverrides as $perm) {
            $overrides[$perm->id] = (bool) $perm->pivot->allowed;
        }

        // permisos del rol
        $rolePermIds = $usuario->role ? $usuario->role->permissions->pluck('id')->all() : [];

        return view('usuarios.edit', [
            'user'                => $usuario,
            'roles'               => $roles,
            'permissionsByModule' => $permissions,
            'overrides'           => $overrides,
            'rolePermIds'         => $rolePermIds,
        ]);
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $usuario->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_id'  => ['nullable', 'integer', 'exists:roles,id'],
            'is_admin' => ['nullable', 'boolean'],
            'perm'     => ['nullable', 'array'],    // perm[permission_id] => '', '1', '0'
        ]);

        $usuario->name  = $data['name'];
        $usuario->email = $data['email'];
        $usuario->role_id  = $data['role_id'] ?? null;
        $usuario->is_admin = ! empty($data['is_admin']);

        if (! empty($data['password'])) {
            $usuario->password = Hash::make($data['password']);
        }

        $usuario->save();

        // gestionar overrides
        $permsInput = $data['perm'] ?? [];

        // limpiamos overrides existentes
        $usuario->permissionOverrides()->detach();

        foreach ($permsInput as $permissionId => $value) {
            if ($value === '' || $value === null) {
                continue; // hereda del rol
            }

            $allowed = $value === '1';
            $usuario->permissionOverrides()->attach($permissionId, ['allowed' => $allowed]);
        }

        return redirect()
            ->route('usuarios.edit', $usuario)
            ->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario): RedirectResponse
    {
        if (auth()->id() === $usuario->id) {
            return redirect()
                ->route('usuarios.index')
                ->with('status', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->delete();

        return redirect()
            ->route('usuarios.index')
            ->with('status', 'Usuario eliminado.');
    }
}
