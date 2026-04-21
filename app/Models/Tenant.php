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
        'printer_modo',
        'printer_ip',
        'printer_puerto',
        'printer_ip_cocina',
        'printer_puerto_cocina',
        'printer_nombre_ticket',
        'printer_nombre_comanda',
        'printer_auto_ticket',
        'printer_auto_comanda',
        'printer_secret_key',
        'printer_width',
        'printer_logo',
        'printer_show_nombre',
    ];

    protected $casts = [
        'bill_date'             => 'date',
        'printer_auto_ticket'   => 'boolean',
        'printer_auto_comanda'  => 'boolean',
        'printer_logo'          => 'boolean',
        'printer_show_nombre'   => 'boolean',
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

    // ── Helpers de impresión ────────────────────────────────────────────────

    /** Devuelve el modo activo: 'browser' | 'escpos' | 'network_ip' */
    public function printerModo(): string
    {
        return $this->printer_modo ?? 'browser';
    }

    /** true si el modo es ESC/POS mediante el agente Windows */
    public function printerEsEscpos(): bool
    {
        return $this->printerModo() === 'escpos';
    }

    /** true si el modo es el agente HTTP local (POST http://localhost:9876) */
    public function printerEsAgent(): bool
    {
        return $this->printerModo() === 'agent';
    }

    /** true si el modo es impresora de red por IP LAN (TCP socket) */
    public function printerEsNetworkIp(): bool
    {
        return $this->printerModo() === 'network_ip';
    }

    /** Endpoint TCP para la impresora de tickets: "ip:puerto" */
    public function printerEndpoint(): string
    {
        return ($this->printer_ip ?? '') . ':' . ($this->printer_puerto ?? 9100);
    }

    /** Endpoint TCP para la impresora de cocina, o null si no está configurada */
    public function printerEndpointCocina(): ?string
    {
        if (empty($this->printer_ip_cocina)) return null;
        return $this->printer_ip_cocina . ':' . ($this->printer_puerto_cocina ?? 9100);
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
