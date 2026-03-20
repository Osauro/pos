<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;
use Livewire\Attributes\Layout;

#[Layout('layouts.guest')]
class Login extends Component
{
    public $celular = '';
    public $pin = '';
    public $remember = false;

    protected $rules = [
        'celular' => 'required|digits:8',
        'pin' => 'required|digits:4',
    ];

    protected $messages = [
        'celular.required' => 'El número de celular es obligatorio',
        'celular.digits' => 'El celular debe tener exactamente 8 dígitos',
        'pin.required' => 'El PIN es obligatorio',
        'pin.digits' => 'El PIN debe tener exactamente 4 dígitos',
    ];

    public function login()
    {
        $this->validate();

        // Buscar usuario por celular
        $usuario = Usuario::where('celular', $this->celular)->first();

        if (!$usuario) {
            $this->addError('celular', 'Usuario no encontrado');
            return;
        }

        // Verificar PIN usando Hash::check ya que está hasheado en la BD
        if (!\Illuminate\Support\Facades\Hash::check($this->pin, $usuario->pin)) {
            $this->addError('pin', 'PIN incorrecto');
            return;
        }

        // Autenticar al usuario
        Auth::login($usuario, $this->remember);

        // Regenerar sesión por seguridad
        request()->session()->regenerate();

        // Redirigir a Ventas
        return redirect()->intended(route('ventas'));
    }

    public function render()
    {
        return view('livewire.login');
    }
}
