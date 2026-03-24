<?php

namespace App\Models;

use App\Helpers\TenantHelper;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    protected $fillable = [
        'tenant_id',
        'turno_id',
        'user_id',
        'detalle',
        'ingreso',
        'egreso',
        'saldo',
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
        'ingreso' => 'decimal:2',
        'egreso' => 'decimal:2',
        'saldo' => 'decimal:2'
    ];

    public function turno()
    {
        return $this->belongsTo(Turno::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
