<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'nombre',
        'celular',
        'pin',
        'tipo'
    ];

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
