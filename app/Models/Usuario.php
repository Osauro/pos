<?php

namespace App\Models;

use App\Helpers\TenantHelper;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'tenant_id',
        'nombre',
        'celular',
        'pin',
        'tipo',
        'is_owner',
    ];

    protected $casts = [
        'is_owner' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Filtrar siempre por tenant (omitir en CLI: seeders / migraciones)
        static::addGlobalScope('tenant', function ($query) {
            if (app()->runningInConsole()) return;
            $table = $query->getModel()->getTable();
            $query->where("{$table}.tenant_id", TenantHelper::currentId() ?? 0);
        });

        // Asignar tenant automáticamente al crear (excepto al landlord)
        static::creating(function ($model) {
            if (!$model->tenant_id && !$model->is_owner) {
                $model->tenant_id = TenantHelper::currentId();
            }
        });
    }

    protected $hidden = [
        'pin',
        'remember_token'
    ];

    // Especificar el campo de contraseña
    public function getAuthPassword()
    {
        return $this->pin;
    }

    public function turnos()
    {
        return $this->hasMany(Turno::class, 'encargado_id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'user_id');
    }
}
