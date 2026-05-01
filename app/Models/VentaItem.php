<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaItem extends Model
{
    protected $fillable = [
        'venta_id',
        'producto_id',
        'cantidad',
        'precio',
        'subtotal',
        'detalle',
        'comanda_impresa',
    ];

    protected $casts = [
        'precio'          => 'decimal:2',
        'subtotal'        => 'decimal:2',
        'detalle'         => 'array',
        'comanda_impresa' => 'boolean',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
