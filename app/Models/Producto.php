<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = [
        'nombre',
        'imagen',
        'precio',
        'tipo',
        'estado',
        'total_vendido',
    ];

    protected $casts = [
        'precio'        => 'decimal:2',
        'estado'        => 'boolean',
        'total_vendido' => 'integer',
    ];

    public function getPhotoUrlAttribute()
    {
        if ($this->imagen) {
            return asset('storage/' . $this->imagen);
        }
        return asset('assets/images/product-placeholder.svg');
    }

    public function ventaItems()
    {
        return $this->hasMany(VentaItem::class);
    }
}
