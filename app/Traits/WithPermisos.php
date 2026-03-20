<?php

namespace App\Traits;

use App\Models\Turno;
use Carbon\Carbon;

trait WithPermisos
{
    // Superadmin ID=1
    public function esSuperAdmin(): bool
    {
        return auth()->check() && auth()->id() === 1;
    }

    // Es admin (tipo = 'admin')
    public function esAdmin(): bool
    {
        return auth()->check() && auth()->user()->tipo === 'admin';
    }

    // Es user (tipo = 'user')
    public function esUser(): bool
    {
        return auth()->check() && auth()->user()->tipo === 'user';
    }

    // Admin con turno de la semana vigente (actual)
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

    // Permisos específicos por módulo
    public function puedeAccederUsuarios(): bool
    {
        return $this->esSuperAdmin() || ($this->esAdmin() && $this->tieneTurnoActivo());
    }

    public function puedeAccederProductos(): bool
    {
        return $this->esSuperAdmin() || ($this->esAdmin() && $this->tieneTurnoActivo());
    }

    public function puedeAccederTurnos(): bool
    {
        return $this->esSuperAdmin() || $this->esAdmin();
    }

    public function puedeAccederPOS(): bool
    {
        return $this->esSuperAdmin() || ($this->esAdmin() && $this->tieneTurnoActivo()) || $this->esUser();
    }

    public function puedeAccederMovimientos(): bool
    {
        return $this->esSuperAdmin() || $this->esAdmin() || $this->esUser();
    }

    public function puedeAccederVentas(): bool
    {
        return $this->esSuperAdmin() || $this->esAdmin() || $this->esUser();
    }

    // Solo el superadmin puede eliminar
    public function puedeEliminar(): bool
    {
        return $this->esSuperAdmin();
    }

    // Redirige si no tiene acceso (usa en mount de cada componente)
    public function verificarAccesoUsuarios(): void
    {
        if (!$this->puedeAccederUsuarios()) {
            $this->redirect(route('pos'));
        }
    }

    public function verificarAccesoProductos(): void
    {
        if (!$this->puedeAccederProductos()) {
            $this->redirect(route('pos'));
        }
    }

    public function verificarAccesoTurnos(): void
    {
        if (!$this->puedeAccederTurnos()) {
            $this->redirect(route('pos'));
        }
    }

    public function verificarAccesoPOS(): void
    {
        if (!$this->puedeAccederPOS()) {
            $this->redirect(route('turnos'));
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
}
