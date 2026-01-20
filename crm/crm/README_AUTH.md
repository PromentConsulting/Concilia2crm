# Auth mínimo para Laravel CRM
Este paquete añade:
- Login/Logout con sesión (guard `web`).
- Vista `/login` y panel `/dashboard` protegido por `auth`.
- Migración `is_admin` en `users` para marcar usuarios administradores.
- Snippets de rutas para instalación y creación del primer admin.

## 1) Copia los archivos
Descomprime este zip **dentro del proyecto** Laravel (respeta `app/`, `resources/`, `database/`).

## 2) Rutas a añadir en `routes/web.php`
```php
use App\Http\Controllers\Auth\LoginController;

Route::get('/login', [LoginController::class, 'show'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'store'])->name('login.post')->middleware('guest');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard')->middleware('auth');
Route::get('/', fn() => redirect()->route('dashboard'));
```

## 3) Ejecutar migraciones (añade `is_admin`)
Sin SSH, crea temporalmente:
```php
use Illuminate\Support\Facades\Artisan;
Route::get('/__migrate/{token}', function($token){
  abort_unless(hash_equals($token, env('INSTALL_TOKEN')), 403);
  Artisan::call('config:clear');
  Artisan::call('migrate', ['--force' => true]);
  return nl2br(e(Artisan::output()));
});
```
Abre `https://tu-dominio.com/__migrate/TU_TOKEN` y luego borra la ruta.

## 4) Crear usuario administrador
Ruta temporal:
```php
use Illuminate\Support\Facades\Hash;
use App\Models\User;

Route::get('/__bootstrap_admin/{token}', function($token){
  abort_unless(hash_equals($token, env('INSTALL_TOKEN')), 403);
  $email = env('ADMIN_EMAIL','admin@example.com');
  $pass  = env('ADMIN_PASSWORD','ChangeMe123!');
  $user = User::firstOrCreate(
    ['email' => $email],
    ['name' => 'Admin', 'password' => Hash::make($pass), 'is_admin' => true]
  );
  if (!$user->is_admin) { $user->is_admin = true; $user->save(); }
  return "Admin listo: {$email} / {$pass}";
});
```
En `.env` añade temporalmente:
```
INSTALL_TOKEN=MiTokenUltraSeguro_123
ADMIN_EMAIL=admin@tudominio.com
ADMIN_PASSWORD=TuPasswordFuerte123!
```
Visita `https://tu-dominio.com/__bootstrap_admin/MiTokenUltraSeguro_123` y **borra** la ruta + variables del `.env` al terminar.

## 5) Notas
- Puedes mantener `SESSION_DRIVER=file` o usar `database` si creas la tabla `sessions`.
- En producción: `APP_ENV=production`, `APP_DEBUG=false`.
- Asegúrate de que `storage/` y `bootstrap/cache` tienen permisos de escritura.
