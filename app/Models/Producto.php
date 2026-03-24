<?php

namespace App\Models;

use App\Helpers\TenantHelper;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = [
        'tenant_id',
        'nombre',
        'imagen',
        'precio',
        'tipo',
        'estado',
        'total_vendido',
    ];

    protected static function booted(): void
    {
        // Filtrar siempre por tenant (omitir en CLI: seeders / migraciones)
        static::addGlobalScope('tenant', function ($query) {
            if (app()->runningInConsole()) return;
            $table = $query->getModel()->getTable();
            $query->where("{$table}.tenant_id", TenantHelper::currentId() ?? 0);
        });

        // Asignar tenant automáticamente al crear
        static::creating(function ($model) {
            if (!$model->tenant_id) {
                $model->tenant_id = TenantHelper::currentId();
            }
        });
    }

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
