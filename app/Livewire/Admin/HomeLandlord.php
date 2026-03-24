<?php

namespace App\Livewire\Admin;

use App\Models\PagoSuscripcion;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.landlord.app')]
class HomeLandlord extends Component
{
    public function render()
    {
        $hoy = Carbon::today();

        $totalTenants    = Tenant::count();
        $tenantsActivos  = Tenant::where('status', 'activo')->count();
        $tenantsVencidos = Tenant::whereNotNull('bill_date')->where('bill_date', '<', $hoy)->count();
        $tenantsProximos = Tenant::where('status', 'activo')
            ->whereNotNull('bill_date')
            ->whereBetween('bill_date', [$hoy, $hoy->copy()->addDays(7)])
            ->count();
        $totalUsuarios   = User::whereDoesntHave('tenants', fn($q) => $q->where('is_super_admin', true))->count();

        $pagosPendientes = PagoSuscripcion::where('estado', 'pendiente')->count();
        $ingresosMes     = PagoSuscripcion::where('estado', 'verificado')
            ->whereYear('verificado_at', $hoy->year)
            ->whereMonth('verificado_at', $hoy->month)
            ->sum('monto');
        $ingresosTotal   = PagoSuscripcion::where('estado', 'verificado')->sum('monto');

        $ultimosPendientes = PagoSuscripcion::with('tenant')
            ->where('estado', 'pendiente')
            ->latest()
            ->limit(6)
            ->get();

        $proximosVencer = Tenant::where('status', 'activo')
            ->whereNotNull('bill_date')
            ->where('bill_date', '>=', $hoy)
            ->orderBy('bill_date')
            ->limit(6)
            ->get();

        $ultimosTenants = Tenant::latest()->limit(5)->get();

        return view('livewire.admin.home-landlord', compact(
            'totalTenants', 'tenantsActivos', 'tenantsVencidos', 'tenantsProximos', 'totalUsuarios',
            'pagosPendientes', 'ingresosMes', 'ingresosTotal',
            'ultimosPendientes', 'proximosVencer', 'ultimosTenants'
        ));
    }
}
