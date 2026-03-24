<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Suscripcion;
use App\Livewire\HomeTenant;
use App\Livewire\Usuarios;
use App\Livewire\Productos;
use App\Livewire\Turnos;
use App\Livewire\Movimientos;
use App\Livewire\Ventas;
use App\Livewire\Pos;
use App\Livewire\Login;
use App\Livewire\Admin\TenantsManager;
use App\Livewire\Admin\HomeLandlord;
use App\Livewire\Admin\PagosManager;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\EscposController;

// -- Manifest din�mico por tenant (PWA) ----------------------------------------
Route::get('/manifest.webmanifest', function () {
    $tenant   = currentTenant();
    $color    = $tenant?->themeColor() ?? '#29adb2';
    $nombre   = $tenant?->nombre ?? config('app.name', 'POS');

    $manifest = [
        'name'             => $nombre . ' — POS',
        'short_name'       => 'POS',
        'description'      => 'Sistema POS — ' . $nombre,
        'start_url'        => '/',
        'display'          => 'standalone',
        'background_color' => '#ffffff',
        'theme_color'      => $color,
        'orientation'      => 'any',
        'scope'            => '/',
        'lang'             => 'es',
        'icons'            => [
            [
                'src'     => '/assets/images/icon-192.png',
                'sizes'   => '192x192',
                'type'    => 'image/png',
                'purpose' => 'any maskable',
            ],
            [
                'src'     => '/assets/images/icon-512.png',
                'sizes'   => '512x512',
                'type'    => 'image/png',
                'purpose' => 'any maskable',
            ],
        ],
    ];

    return response()->json($manifest)
        ->header('Content-Type', 'application/manifest+json');
})->name('pwa.manifest');

// -- Descarga agente de impresión ----------------------------------------------
Route::get('/download/printpos', function () {
    $path = public_path('printPOS.zip');
    abort_unless(file_exists($path), 404);
    return response()->download($path, 'printPOS.zip', [
        'Content-Type' => 'application/zip',
    ]);
})->name('download.printpos')->middleware('auth');

// -- Rutas p�blicas ------------------------------------------------------------
Route::get('/login', Login::class)->name('login')->middleware('guest');

Route::post('/logout', function () {
    Auth::logout();
    \App\Helpers\TenantHelper::clear();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

Route::post('/tenant/switch', function () {
    $tenantId = (int) request('tenant_id');
    if (auth()->check() && auth()->user()->switchTenant($tenantId)) {
        // Si viene con redirect explícito (ej: desde landlord panel), usarlo
        $redirectTo = request('redirect') ?? route('ventas');
        return redirect($redirectTo);
    }
    return redirect()->back()->with('error', 'No puedes cambiar a ese negocio.');
})->name('tenant.switch')->middleware('auth');

// -- Panel del Landlord (super admin del sistema) ------------------------------
Route::middleware(['auth', 'landlord'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', HomeLandlord::class)->name('dashboard');
    Route::get('/negocios',  TenantsManager::class)->name('tenants');
    Route::get('/pagos',     PagosManager::class)->name('pagos');
});

// -- Crear tienda (cualquier usuario autenticado) ----------------------------
Route::get('/crear-tienda', \App\Livewire\Admin\CrearTienda::class)->name('crear-tienda')->middleware('auth');

// -- Rutas del negocio (requieren tenant activo en sesi�n) ---------------------
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/', fn() => redirect()->route('dashboard'));

    // Dashboard tenant
    Route::get('/dashboard', HomeTenant::class)->name('dashboard');

    // Gesti�n (solo admin/landlord)
    Route::middleware(['tenant.manage'])->group(function () {
        Route::get('/usuarios',     Usuarios::class)->name('usuarios');
        Route::get('/productos',    Productos::class)->name('productos');
        Route::get('/turnos',       Turnos::class)->name('turnos');
    });

    // Suscripción: accesible aunque el tenant esté inactivo (para mostrar QR de pago)
    Route::get('/suscripcion',  Suscripcion::class)->name('suscripcion');

    // Movimientos: accesible por admin y operadores (restricciones internas en el componente)
    Route::get('/movimientos', Movimientos::class)->name('movimientos');

    // Acceso general (admin + operador)
    Route::get('/ventas', Ventas::class)->name('ventas');
    Route::get('/pos',    Pos::class)->name('pos');

    // Tickets
    Route::get('/ticket/comanda/{venta}',     [TicketController::class, 'comanda'])->name('ticket.comanda');
    Route::get('/ticket/cliente/{venta}',     [TicketController::class, 'cliente'])->name('ticket.cliente');
    Route::get('/ticket/venta/{venta}',       [TicketController::class, 'venta'])->name('ticket.venta');
    Route::get('/ticket/cliente/{venta}/pdf', [TicketController::class, 'clientePdf'])->name('ticket.cliente.pdf');
    Route::get('/ticket/comanda/{venta}/pdf', [TicketController::class, 'comandaPdf'])->name('ticket.comanda.pdf');
});

// -- ESC/POS directo (agente print-agent) -------------------------------------
Route::prefix('escpos')->group(function () {
    Route::get('/ticket/{venta}',  [EscposController::class, 'ticket'])->name('escpos.ticket');
    Route::get('/comanda/{venta}', [EscposController::class, 'comanda'])->name('escpos.comanda');
});
