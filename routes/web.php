<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Usuarios;
use App\Livewire\Productos;
use App\Livewire\Turnos;
use App\Livewire\Movimientos;
use App\Livewire\Ventas;
use App\Livewire\Pos;
use App\Livewire\Login;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\EscposController;

// Rutas públicas (login)
Route::get('/login', Login::class)->name('login')->middleware('guest');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Rutas protegidas con autenticación
Route::middleware(['auth'])->group(function () {
    // Redirigir raíz al POS
    Route::get('/', function () {
        return redirect('/pos');
    });

    // Rutas del sistema
    Route::get('/usuarios', Usuarios::class)->name('usuarios');
    Route::get('/productos', Productos::class)->name('productos');
    Route::get('/turnos', Turnos::class)->name('turnos');
    Route::get('/movimientos', Movimientos::class)->name('movimientos');
    Route::get('/ventas', Ventas::class)->name('ventas');
    Route::get('/pos', Pos::class)->name('pos');

    // Tickets / Comandas (también accesibles en nueva pestaña como fallback)
    Route::get('/ticket/comanda/{venta}',     [TicketController::class, 'comanda'])->name('ticket.comanda');
    Route::get('/ticket/cliente/{venta}',     [TicketController::class, 'cliente'])->name('ticket.cliente');
    Route::get('/ticket/venta/{venta}',       [TicketController::class, 'venta'])->name('ticket.venta');
    // Versiones PDF (para impresión en móvil)
    Route::get('/ticket/cliente/{venta}/pdf', [TicketController::class, 'clientePdf'])->name('ticket.cliente.pdf');
    Route::get('/ticket/comanda/{venta}/pdf', [TicketController::class, 'comandaPdf'])->name('ticket.comanda.pdf');
});

// ── ESC/POS directo (agente print-agent.php) ──────────────────────────────────
// Protegido por token (X-Printer-Token header o ?_pt=...)
// Solo accesible desde la máquina local (el agente corre en el mismo equipo)
Route::prefix('escpos')->group(function () {
    Route::get('/ticket/{venta}',  [EscposController::class, 'ticket'])->name('escpos.ticket');
    Route::get('/comanda/{venta}', [EscposController::class, 'comanda'])->name('escpos.comanda');
});

