<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\DocenteDisponibilidad;
use App\Models\PedidoDocenteHorario;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin', // campo de la migración que ya tenías
        'role_id',
        'two_factor_code',
        'two_factor_expires_at',
        'dashboard_layout',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_admin'          => 'boolean',
        'two_factor_expires_at' => 'datetime',
        'dashboard_layout' => 'array',
    ];

    /**
     * Rol asignado al usuario.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
    
    public function disponibilidades(): HasMany
    {
        return $this->hasMany(DocenteDisponibilidad::class, 'user_id');
    }

    public function horariosFormacion(): HasMany
    {
        return $this->hasMany(PedidoDocenteHorario::class, 'user_id');
    }

    /**
     * Overrides de permisos a nivel de usuario (permiso_user).
     */
    public function permissionOverrides()
    {
        return $this->belongsToMany(Permission::class, 'permission_user')
            ->withPivot('allowed')
            ->withTimestamps();
    }

    /**
     * Permisos que vienen del rol del usuario.
     */
    public function rolePermissions()
    {
        return $this->role ? $this->role->permissions : collect();
    }

    /**
     * Comprueba si el usuario tiene un permiso (rol + overrides).
     */
    public function hasPermission(string $key): bool
    {
        // Admin = todos los permisos
        if ($this->is_admin) {
            return true;
        }

        $permission = Permission::where('key', $key)->first();
        if (! $permission) {
            return false;
        }

        // Override explícito del usuario
        $override = $this->permissionOverrides()
            ->where('permission_id', $permission->id)
            ->first();

        if ($override) {
            return (bool) $override->pivot->allowed;
        }

        // Si no hay override, miramos los permisos del rol
        if ($this->role) {
            return $this->rolePermissions()->contains('id', $permission->id);
        }

        return false;
    }
}
