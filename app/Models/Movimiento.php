<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    protected $fillable = [
        'turno_id',
        'detalle',
        'ingreso',
        'egreso',
        'saldo'
    ];

    protected $casts = [
        'ingreso' => 'decimal:2',
        'egreso' => 'decimal:2',
        'saldo' => 'decimal:2'
    ];

    public function turno()
    {
        return $this->belongsTo(Turno::class);
    }
}
