<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'nombre',
        'slug',
        'telefono',
        'direccion',
        'logo',
        'status',
        'bill_date',
        'theme_number',
    ];

    protected $casts = [
        'bill_date' => 'date',
    ];

    public function themeColor(): string
    {
        $colores = [
            2  => '#f73164',
            3  => '#29adb2',
            4  => '#6610f2',
            5  => '#dc3545',
            6  => '#f57f17',
            7  => '#0288d1',
            8  => '#00897b',
            9  => '#558b2f',
            10 => '#455a64',
        ];
        return $colores[$this->theme_number ?? 3] ?? '#29adb2';
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->withPivot('role', 'is_active')
            ->withTimestamps();
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'tenant_id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'tenant_id');
    }

    public function turnos()
    {
        return $this->hasMany(Turno::class, 'tenant_id');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'tenant_id');
    }

    public function pagosSuscripcion()
    {
        return $this->hasMany(PagoSuscripcion::class, 'tenant_id');
    }

    public function isActivo(): bool
    {
        if ($this->status !== 'activo') {
            return false;
        }

        if ($this->bill_date && $this->bill_date->isPast()) {
            return false;
        }

        return true;
    }
}
