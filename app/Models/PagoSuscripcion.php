<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoSuscripcion extends Model
{
    protected $table = 'pagos_suscripcion';

    protected $fillable = [
        'tenant_id',
        'comprobante_path',
        'monto',
        'notas',
        'estado',
        'notas_verificacion',
        'verificado_by',
        'verificado_at',
    ];

    protected $casts = [
        'verificado_at' => 'datetime',
        'monto'         => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function verificadoPor()
    {
        return $this->belongsTo(User::class, 'verificado_by');
    }

    public function isPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }
}
