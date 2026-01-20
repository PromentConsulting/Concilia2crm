<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('description')->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique(); // ej: accounts.view
                $table->string('name');
                $table->string('module')->nullable(); // ej: cuentas, contactos
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id');
                $table->unsignedBigInteger('permission_id');
                $table->primary(['role_id', 'permission_id']);
                $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
                $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('permission_user')) {
            Schema::create('permission_user', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('permission_id');
                $table->boolean('allowed'); // true = permitir, false = denegar
                $table->timestamps();

                $table->primary(['user_id', 'permission_id']);
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            });
        }

        // Semilla básica de permisos por módulos principales
        if (Schema::hasTable('permissions')) {
            $now = now();

            $perms = [
                // Dashboard
                ['key' => 'dashboard.view', 'name' => 'Ver dashboard', 'module' => 'dashboard'],

                // Cuentas
                ['key' => 'accounts.view',   'name' => 'Ver cuentas',   'module' => 'cuentas'],
                ['key' => 'accounts.create', 'name' => 'Crear cuentas', 'module' => 'cuentas'],
                ['key' => 'accounts.update', 'name' => 'Editar cuentas','module' => 'cuentas'],
                ['key' => 'accounts.delete', 'name' => 'Eliminar cuentas','module' => 'cuentas'],

                // Contactos
                ['key' => 'contacts.view',   'name' => 'Ver contactos',   'module' => 'contactos'],
                ['key' => 'contacts.create', 'name' => 'Crear contactos', 'module' => 'contactos'],
                ['key' => 'contacts.update', 'name' => 'Editar contactos','module' => 'contactos'],
                ['key' => 'contacts.delete', 'name' => 'Eliminar contactos','module' => 'contactos'],

                // Solicitudes
                ['key' => 'solicitudes.view',   'name' => 'Ver solicitudes',   'module' => 'solicitudes'],
                ['key' => 'solicitudes.create', 'name' => 'Crear solicitudes', 'module' => 'solicitudes'],
                ['key' => 'solicitudes.update', 'name' => 'Editar solicitudes','module' => 'solicitudes'],
                ['key' => 'solicitudes.delete', 'name' => 'Eliminar solicitudes','module' => 'solicitudes'],

                // Peticiones
                ['key' => 'peticiones.view',   'name' => 'Ver peticiones',   'module' => 'peticiones'],
                ['key' => 'peticiones.create', 'name' => 'Crear peticiones', 'module' => 'peticiones'],
                ['key' => 'peticiones.update', 'name' => 'Editar peticiones','module' => 'peticiones'],
                ['key' => 'peticiones.delete', 'name' => 'Eliminar peticiones','module' => 'peticiones'],

                // Pedidos
                ['key' => 'pedidos.view',   'name' => 'Ver pedidos',   'module' => 'pedidos'],
                ['key' => 'pedidos.create', 'name' => 'Crear pedidos', 'module' => 'pedidos'],
                ['key' => 'pedidos.update', 'name' => 'Editar pedidos','module' => 'pedidos'],
                ['key' => 'pedidos.delete', 'name' => 'Eliminar pedidos','module' => 'pedidos'],

                // Usuarios
                ['key' => 'users.view',    'name' => 'Ver usuarios',   'module' => 'usuarios'],
                ['key' => 'users.manage',  'name' => 'Gestionar usuarios', 'module' => 'usuarios'],

                // Roles
                ['key' => 'roles.view',    'name' => 'Ver roles',      'module' => 'roles'],
                ['key' => 'roles.manage',  'name' => 'Gestionar roles','module' => 'roles'],
            ];

            foreach ($perms as $perm) {
                $exists = DB::table('permissions')->where('key', $perm['key'])->exists();
                if (! $exists) {
                    DB::table('permissions')->insert([
                        'key'         => $perm['key'],
                        'name'        => $perm['name'],
                        'module'      => $perm['module'],
                        'description' => $perm['name'],
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
