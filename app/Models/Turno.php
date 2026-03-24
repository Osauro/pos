<?php

namespace App\Models;

use App\Helpers\TenantHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    protected $fillable = [
        'tenant_id',
        'encargado_id',
        'fecha_inicio',
        'fecha_fin',
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
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    // Determina el estado real basado en fechas
    public function getEstadoRealAttribute(): string
    {
        $hoy = Carbon::today();
        if ($hoy->between($this->fecha_inicio, $this->fecha_fin)) {
            return 'activo';
        }
        return 'finalizado';
    }

    // Scope: turno activo hoy
    public function scopeActivo($query)
    {
        $hoy = Carbon::today()->toDateString();
        return $query->where('fecha_inicio', '<=', $hoy)
                     ->where('fecha_fin', '>=', $hoy);
    }

    // Verifica si un rango se solapa con turnos existentes (excluyendo $exceptId)
    public static function rangoOcupado(string $inicio, string $fin, ?int $exceptId = null): bool
    {
        return static::where('fecha_inicio', '<=', $fin)
            ->where('fecha_fin', '>=', $inicio)
            ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
            ->exists();
    }

    public function encargado()
    {
        return $this->belongsTo(User::class, 'encargado_id');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class);
    }
}
