<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountViewController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactViewController;
use App\Http\Controllers\PedidoViewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\FacturaViewController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PeticionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\SolicitudReglaController;
use App\Http\Controllers\SolicitudViewController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TareaController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\DocenteCalendarioController;
use App\Http\Controllers\IntegracionController;
use App\Http\Controllers\PeticionLineaController;
use App\Http\Controllers\InformeController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\AccessLogController;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->name('login.post')
        ->middleware('throttle:5,1');

    Route::get('/two-factor-challenge', [TwoFactorController::class, 'show'])
        ->name('two-factor.show');

    Route::post('/two-factor-challenge', [TwoFactorController::class, 'store'])
        ->name('two-factor.store')
        ->middleware('throttle:5,1');

    Route::post('/two-factor-challenge/resend', [TwoFactorController::class, 'resend'])
        ->name('two-factor.resend')
        ->middleware('throttle:3,1');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth');

/*
|--------------------------------------------------------------------------
| APP PROTEGIDA
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/dashboard/layout', [DashboardController::class, 'updateLayout'])->name('dashboard.layout.update');
    Route::get('/', fn () => redirect()->route('dashboard'));

    /*
    |---------------- SOLICITUDES: REGLAS + CRUD ----------------
    |  ⚠️ Las rutas de reglas VAN ANTES del resource('solicitudes')
    */

    Route::get('solicitudes/reglas', [SolicitudReglaController::class, 'index'])
        ->name('solicitudes.reglas.index');

    Route::post('solicitudes/reglas', [SolicitudReglaController::class, 'store'])
        ->name('solicitudes.reglas.store');

    Route::delete('solicitudes/reglas/{regla}', [SolicitudReglaController::class, 'destroy'])
        ->name('solicitudes.reglas.destroy');

    Route::resource('solicitudes', SolicitudController::class)
        ->parameters(['solicitudes' => 'solicitud']);

    /*
    |---------------- RESTO DE MÓDULOS ----------------
    */

    Route::post('accounts/bulk', [AccountController::class, 'bulk'])->name('accounts.bulk');
    Route::get('accounts/export', [AccountController::class, 'export'])->name('accounts.export');

    Route::get('accounts/{account}/quick', [AccountController::class, 'quick'])->name('accounts.quick');
    Route::resource('accounts', AccountController::class);

    Route::get('contacts/export', [ContactController::class, 'export'])->name('contacts.export');
    Route::post('contacts/bulk', [ContactController::class, 'bulk'])->name('contacts.bulk');
    Route::resource('contacts', ContactController::class);

    Route::resource('peticiones', PeticionController::class)
        ->parameters(['peticiones' => 'peticion']);

    Route::resource('pedidos', PedidoController::class)
        ->parameters(['pedidos' => 'pedido']);
    Route::post('pedidos/{pedido}/docentes', [PedidoController::class, 'syncDocentes'])
        ->name('pedidos.docentes.sync');
    Route::post('pedidos/{pedido}/docentes/horarios', [PedidoController::class, 'storeDocenteHorario'])
        ->name('pedidos.docentes.horarios.store');
    Route::delete('pedidos/{pedido}/docentes/horarios/{horario}', [PedidoController::class, 'destroyDocenteHorario'])
        ->name('pedidos.docentes.horarios.destroy');

    Route::get('facturas/{factura}/pdf', [FacturaController::class, 'pdf'])
        ->name('facturas.pdf');
    Route::post('facturas/{factura}/publicar', [FacturaController::class, 'publish'])
        ->name('facturas.publish');
    Route::post('facturas/{factura}/rectificar', [FacturaController::class, 'rectificar'])
        ->name('facturas.rectificar');

    Route::resource('facturas', FacturaController::class)
        ->only(['index', 'show', 'create', 'store', 'edit', 'update', 'destroy'])
        ->parameters(['facturas' => 'factura']);
    Route::get('docentes/calendario', [DocenteCalendarioController::class, 'index'])
        ->name('docentes.calendario.index');
    Route::get('docentes/{docente}/calendario', [DocenteCalendarioController::class, 'show'])
        ->name('docentes.calendario.show');
    Route::post('docentes/{docente}/calendario', [DocenteCalendarioController::class, 'store'])
        ->name('docentes.calendario.store');
    Route::delete('docentes/calendario/{disponibilidad}', [DocenteCalendarioController::class, 'destroy'])
        ->name('docentes.calendario.destroy');

    Route::prefix('catalogo')->name('catalogo.')->group(function () {
        Route::resource('servicios', ServiceController::class)
            ->parameters(['servicios' => 'servicio']);

        Route::post('categorias', [ServiceCategoryController::class, 'store'])
            ->name('categorias.store');

        Route::delete('categorias/{categoria}', [ServiceCategoryController::class, 'destroy'])
            ->name('categorias.destroy');
    });

    Route::resource('usuarios', UserController::class)->names('usuarios');
    Route::resource('roles', RoleController::class)->names('roles');

    Route::resource('tareas', TareaController::class)
        ->parameters(['tareas' => 'tarea']);

    Route::resource('documentos', DocumentoController::class)
        ->only(['index', 'create', 'store', 'destroy'])
        ->names('documentos');

    Route::resource('campaigns', CampaignController::class);

    Route::get('/configuracion', [ConfiguracionController::class, 'index'])
        ->name('configuracion.index');

    Route::post('/configuracion/mautic', [ConfiguracionController::class, 'updateMautic'])
        ->name('configuracion.mautic');

    Route::post('/configuracion/mautic/test', [ConfiguracionController::class, 'testMautic'])
        ->name('configuracion.mautic.test');

    // ✅ NUEVO: iniciar OAuth (Conectar con Mautic)
    Route::match(['get', 'post'], '/configuracion/mautic/connect', [ConfiguracionController::class, 'connectMautic'])
        ->name('configuracion.mautic.connect');

    // ✅ NUEVO: callback OAuth
    Route::get('/configuracion/mautic/callback', [ConfiguracionController::class, 'callbackMautic'])
        ->name('configuracion.mautic.callback');

    Route::get('documentos/{documento}/descargar', [DocumentoController::class, 'download'])
        ->name('documentos.download');

    Route::get('/integraciones', [IntegracionController::class, 'index'])
        ->name('integraciones.index');

    Route::post('/integraciones/tokens', [IntegrationController::class, 'store'])
        ->name('integrations.tokens.store');

    Route::delete('/integraciones/tokens/{token}', [IntegrationController::class, 'destroy'])
        ->name('integrations.tokens.destroy');

    Route::post('peticiones/{peticion}/lineas', [PeticionLineaController::class, 'store'])
        ->name('peticiones.lineas.store');

    Route::delete('peticiones/{peticion}/lineas/{linea}', [PeticionLineaController::class, 'destroy'])
        ->name('peticiones.lineas.destroy');

    Route::get('/informes', [InformeController::class, 'index'])
        ->name('informes.index');

    // Alertas
    Route::get('/alertas', [AlertaController::class, 'index'])
        ->name('alertas.index');

    Route::get('/alertas/configuracion', [AlertaController::class, 'edit'])
        ->name('alertas.edit');

    Route::post('/alertas/configuracion', [AlertaController::class, 'update'])
        ->name('alertas.update');

    Route::get('/logs-accesos', [AccessLogController::class, 'index'])
        ->name('logs-accesos.index');

    Route::get('/accesos', [AccessLogController::class, 'index'])
        ->name('accesos.index');

    Route::post('/solicitudes/bulk', [\App\Http\Controllers\SolicitudController::class, 'bulk'])
        ->name('solicitudes.bulk');

    /*
    |---------------- VISTAS DE CUENTAS ----------------
    */

    Route::post('/accounts/views', [AccountViewController::class, 'store'])
        ->name('accounts.views.store');

    Route::delete('/accounts/views/{view}', [AccountViewController::class, 'destroy'])
        ->name('accounts.views.destroy');

    /*
    |---------------- VISTAS DE CONTACTOS ----------------
    */

    Route::post('/contacts/views', [ContactViewController::class, 'store'])
        ->name('contacts.views.store');

    Route::delete('/contacts/views/{view}', [ContactViewController::class, 'destroy'])
        ->name('contacts.views.destroy');

    /*
    |---------------- VISTAS ----------------
    */

    Route::post('/solicitudes/views', [SolicitudViewController::class, 'store'])
        ->name('solicitudes.views.store');

    Route::delete('/solicitudes/views/{view}', [SolicitudViewController::class, 'destroy'])
        ->name('solicitudes.views.destroy');

    Route::post('/pedidos/views', [PedidoViewController::class, 'store'])
        ->name('pedidos.views.store');

    Route::delete('/pedidos/views/{view}', [PedidoViewController::class, 'destroy'])
        ->name('pedidos.views.destroy');

    Route::post('/facturas/views', [FacturaViewController::class, 'store'])
        ->name('facturas.views.store');

    Route::delete('/facturas/views/{view}', [FacturaViewController::class, 'destroy'])
        ->name('facturas.views.destroy');

    /*
    |---------------- IMPORTACIÓN CUENTAS ----------------
    */

    Route::get('cuentas/importar', [AccountController::class, 'showImportForm'])
        ->name('accounts.import.create');

    Route::get('cuentas/importar/plantilla', [AccountController::class, 'downloadImportTemplate'])
        ->name('accounts.import.template');

    Route::post('cuentas/importar', [AccountController::class, 'handleImport'])
        ->name('accounts.import.store');
});

/*
|--------------------------------------------------------------------------
| UTILIDADES (solo cuando las necesites)
|--------------------------------------------------------------------------
*/

Route::get('/run-migrations-temporal', function () {
    if (request('token') !== env('MIGRATE_TOKEN')) {
        abort(403, 'No autorizado');
    }

    Artisan::call('migrate', ['--force' => true]);

    return nl2br(Artisan::output());
});

Route::get('/debug-public', function () {
    return [
        'public_path'      => public_path(),
        'document_root'    => $_SERVER['DOCUMENT_ROOT'] ?? null,
        'script_filename'  => $_SERVER['SCRIPT_FILENAME'] ?? null,
        'theme_exists'     => file_exists(public_path('assets/theme.css')),
        'logo_full'        => file_exists(public_path('assets/logo-full.webp')),
        'logo_icon'        => file_exists(public_path('assets/logo-icon.webp')),
    ];
});
