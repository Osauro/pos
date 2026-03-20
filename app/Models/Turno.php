<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    protected $fillable = [
        'encargado_id',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

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
        return $this->belongsTo(Usuario::class, 'encargado_id');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class);
    }
}
