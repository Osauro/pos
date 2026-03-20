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
        'estado'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'estado' => 'boolean'
    ];

    public function getPhotoUrlAttribute()
    {
        if ($this->imagen) {
            return asset('storage/' . $this->imagen);
        }
        return asset('assets/images/default-product.png');
    }

    public function ventaItems()
    {
        return $this->hasMany(VentaItem::class);
    }
}
