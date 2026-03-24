<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class Register extends Component
{
    public string $nombre   = '';
    public string $celular  = '';
    public string $pin      = '';
    public string $pin_confirmation = '';

    protected array $rules = [
        'nombre'           => 'required|string|min:2|max:100',
        'celular'          => 'required|digits:8|unique:users,celular',
        'pin'              => 'required|digits:4|same:pin_confirmation',
        'pin_confirmation' => 'required|digits:4',
    ];

    protected array $messages = [
        'nombre.required'           => 'El nombre es obligatorio.',
        'nombre.min'                => 'El nombre debe tener al menos 2 caracteres.',
        'celular.required'          => 'El número de celular es obligatorio.',
        'celular.digits'            => 'El celular debe tener exactamente 8 dígitos.',
        'celular.unique'            => 'Este celular ya está registrado. ¿Quieres iniciar sesión?',
        'pin.required'              => 'El PIN es obligatorio.',
        'pin.digits'                => 'El PIN debe tener exactamente 4 dígitos.',
        'pin.same'                  => 'Los PINs no coinciden.',
        'pin_confirmation.required' => 'Confirma tu PIN.',
        'pin_confirmation.digits'   => 'El PIN de confirmación debe tener 4 dígitos.',
    ];

    public function register(): void
    {
        $this->validate();

        $user = User::create([
            'nombre'  => $this->nombre,
            'celular' => $this->celular,
            'pin'     => Hash::make($this->pin),
        ]);

        Auth::login($user);
        request()->session()->regenerate();

        $this->redirect(route('crear-tienda'), navigate: false);
    }

    public function render()
    {
        return view('livewire.register');
    }
}
