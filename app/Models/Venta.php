<?php

namespace App\Models;

use App\Helpers\TenantHelper;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'turno_id',
        'numero_venta',
        'numero_folio',
        'fecha_hora',
        'total',
        'efectivo',
        'online',
        'credito',
        'estado',
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
        'fecha_hora' => 'datetime',
        'total' => 'decimal:2',
        'efectivo' => 'decimal:2',
        'online' => 'decimal:2',
        'credito' => 'decimal:2'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
        return $this->belongsTo(User::class, 'cliente_id');
    }
}
