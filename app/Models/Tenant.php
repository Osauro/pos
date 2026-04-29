<?php

namespace App\Models;

use Carbon\Carbon;
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
        'horario_inicio',
        'horario_fin',
        'wa_instance_id',
        'wa_api_token',
        'wa_notify_ventas',
    ];

    protected $casts = [
        'bill_date'             => 'date',
        'printer_auto_ticket'   => 'boolean',
        'printer_auto_comanda'  => 'boolean',
        'printer_logo'          => 'boolean',
        'printer_show_nombre'   => 'boolean',
        'wa_notify_ventas'      => 'boolean',
    ];

    // ── Helpers de día comercial ────────────────────────────────────────────

    /**
     * Indica si el horario de atención está configurado y cruza medianoche.
     * Ej: inicio=13:00 fin=02:00 → cruza medianoche.
     */
    /**
     * Devuelve el user_id del propietario del tenant (el registro más antiguo en tenant_user).
     */
    public function propietarioId(): ?int
    {
        return \Illuminate\Support\Facades\DB::table('tenant_user')
            ->where('tenant_id', $this->id)
            ->orderBy('id')
            ->value('user_id');
    }

    public function horarioCruzaMedianoche(): bool
    {
        if (!$this->horario_inicio || !$this->horario_fin) return false;
        return $this->horario_inicio > $this->horario_fin;
    }

    /**
     * Devuelve la fecha de negocio (día comercial) para un datetime dado.
     * Ej: martes 01:30 con horario 13:00-02:00 → devuelve lunes.
     */
    public function businessDayFor(Carbon $dt): Carbon
    {
        if (!$this->horario_inicio || !$this->horario_fin) {
            return $dt->copy()->startOfDay();
        }

        $hora = $dt->format('H:i:s');

        if ($this->horarioCruzaMedianoche()) {
            // Horas "pequeñas" (ej: 00:00-02:00) pertenecen al día anterior
            if ($hora < $this->horario_fin) {
                return $dt->copy()->subDay()->startOfDay();
            }
        }

        return $dt->copy()->startOfDay();
    }

    /**
     * Indica si el datetime dado (o ahora) está dentro del horario de atención.
     * Sin horario configurado → siempre devuelve true.
     */
    public function estaEnHorario(?Carbon $dt = null): bool
    {
        if (!$this->horario_inicio || !$this->horario_fin) return true;

        $dt   = $dt ?? Carbon::now();
        $hora = $dt->format('H:i:s');

        if ($this->horarioCruzaMedianoche()) {
            // Dentro del horario: desde horario_inicio hasta medianoche
            // O desde medianoche hasta horario_fin
            return $hora >= $this->horario_inicio || $hora < $this->horario_fin;
        }

        return $hora >= $this->horario_inicio && $hora < $this->horario_fin;
    }

    /**
     * Devuelve el rango [inicio, fin] de un día comercial dado.
     * Ej: businessDayRange(Monday) con horario 13:00-02:00
     *     → [Monday 13:00:00, Tuesday 02:00:00]
     */
    public function businessDayRange(Carbon $date): array
    {
        $d = $date->copy()->startOfDay();

        if (!$this->horario_inicio || !$this->horario_fin) {
            return [$d->copy()->startOfDay(), $d->copy()->endOfDay()];
        }

        $inicio = $d->copy()->setTimeFromTimeString($this->horario_inicio);

        if ($this->horarioCruzaMedianoche()) {
            $fin = $d->copy()->addDay()->setTimeFromTimeString($this->horario_fin);
        } else {
            $fin = $d->copy()->setTimeFromTimeString($this->horario_fin);
        }

        return [$inicio, $fin];
    }

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

    /**
     * Devuelve el día comercial actual según el horario configurado.
     */
    public function businessDayHoy(): Carbon
    {
        return $this->businessDayFor(Carbon::now());
    }
}
