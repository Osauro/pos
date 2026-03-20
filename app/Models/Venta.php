<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $fillable = [
        'user_id',
        'turno_id',
        'numero_venta',
        'numero_folio',
        'fecha_hora',
        'total',
        'efectivo',
        'online',
        'credito',
        'estado'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'total' => 'decimal:2',
        'efectivo' => 'decimal:2',
        'online' => 'decimal:2',
        'credito' => 'decimal:2'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    public function turno()
    {
        return $this->belongsTo(Turno::class);
    }

    public function items()
    {
        return $this->hasMany(VentaItem::class);
    }

    public function ventaItems()
    {
        return $this->hasMany(VentaItem::class);
    }

    public function cliente()
    {
        // Relación temporal - ajustar según tu estructura
        return $this->belongsTo(Usuario::class, 'cliente_id');
    }
}
