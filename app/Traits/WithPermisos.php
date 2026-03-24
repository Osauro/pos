<?php

namespace App\Traits;

use App\Models\Turno;
use Carbon\Carbon;

trait WithPermisos
{
    // Landlord (super admin del sistema)
    public function esSuperAdmin(): bool
    {
        return isLandlord();
    }

    // Admin del negocio (landlord también cuenta como admin)
    public function esAdmin(): bool
    {
        return isTenantAdmin();
    }

    // Operador (solo POS/ventas)
    public function esUser(): bool
    {
        return auth()->check() && ! isTenantAdmin();
    }

    // Admin con turno propio de la semana vigente
    public function tieneTurnoActivo(): bool
    {
        if (!auth()->check()) return false;

        $inicioSemana = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
        $finSemana    = now()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(6)->toDateString();

        return Turno::where('encargado_id', auth()->id())
            ->where('fecha_inicio', '<=', $finSemana)
            ->where('fecha_fin', '>=', $inicioSemana)
            ->exists();
    }

    // Existe algún turno abierto esta semana (para validar acceso de operadores)
    public function hayTurnoEnSemanaActual(): bool
    {
        $inicioSemana = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
        $finSemana    = now()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(6)->toDateString();

        return Turno::where('tenant_id', currentTenantId())
            ->where('fecha_inicio', '<=', $finSemana)
            ->where('fecha_fin', '>=', $inicioSemana)
            ->exists();
    }

    // Permisos específicos por módulo
    public function puedeAccederUsuarios(): bool
    {
        return $this->esSuperAdmin() || $this->esAdmin();
    }

    public function puedeAccederProductos(): bool
    {
        return $this->esSuperAdmin() || $this->esAdmin();
    }

    public function puedeAccederTurnos(): bool
    {
        return $this->esSuperAdmin() || $this->esAdmin();
    }

    public function puedeAccederPOS(): bool
    {
        if ($this->esSuperAdmin()) return true;
        if ($this->esAdmin()) return $this->tieneTurnoActivo();
        return $this->hayTurnoEnSemanaActual(); // operador: solo si hay turno abierto
    }

    public function puedeAccederDashboard(): bool
    {
        return $this->esSuperAdmin() || $this->esAdmin();
    }

    public function puedeAccederMovimientos(): bool
    {
        return $this->esSuperAdmin() || $this->esAdmin() || $this->esUser();
    }

    public function puedeAccederVentas(): bool
    {
        return $this->esSuperAdmin() || $this->esAdmin() || $this->esUser();
    }

    public function puedeAccederSuscripcion(): bool
    {
        return $this->esAdmin(); // Solo el admin del tenant ve su suscripción
    }

    // Solo admin/landlord pueden crear ingresos; operadores solo egresos
    public function puedeCrearIngreso(): bool
    {
        return $this->esSuperAdmin() || $this->esAdmin();
    }

    // Admin del tenant o landlord pueden eliminar
    public function puedeEliminar(): bool
    {
        return $this->esSuperAdmin() || $this->esAdmin();
    }

    // Redirige si no tiene acceso (usa en mount de cada componente)
    public function verificarAccesoUsuarios(): void
    {
        if (!$this->puedeAccederUsuarios()) {
            $this->redirect($this->esAdmin() ? route('turnos') : route('pos'));
        }
    }

    public function verificarAccesoProductos(): void
    {
        if (!$this->puedeAccederProductos()) {
            $this->redirect($this->esAdmin() ? route('turnos') : route('pos'));
        }
    }

    public function verificarAccesoTurnos(): void
    {
        if (!$this->puedeAccederTurnos()) {
            $this->redirect(route('pos'));
        }
    }

    public function verificarAccesoDashboard(): void
    {
        if (!$this->puedeAccederDashboard()) {
            $this->redirect(route('ventas'));
        }
    }

    public function verificarAccesoPOS(): void
    {
        if (!$this->puedeAccederPOS()) {
            $this->redirect($this->esAdmin() ? route('turnos') : route('ventas'));
        }
    }

    public function verificarAccesoMovimientos(): void
    {
        if (!$this->puedeAccederMovimientos()) {
            $this->redirect(route('pos'));
        }
    }

    public function verificarAccesoVentas(): void
    {
        if (!$this->puedeAccederVentas()) {
            $this->redirect(route('pos'));
        }
    }

    public function verificarAccesoSuscripcion(): void
    {
        if (!$this->puedeAccederSuscripcion()) {
            $this->redirect(route('pos'));
        }
    }
}
