<?php

namespace App\Livewire\Admin;

use App\Models\Tenant;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

#[Layout('layouts.overlay')]
class CrearTienda extends Component
{
    #[Rule('required|min:2|max:100')]
    public string $nombre = '';

    #[Rule('nullable|max:20')]
    public string $telefono = '';

    #[Rule('nullable|max:200')]
    public string $direccion = '';

    #[Rule('required|integer|min:2|max:10')]
    public int $theme_number = 3;

    public function save(): void
    {
        $this->validate();

        $tenant = Tenant::create([
            'nombre'       => $this->nombre,
            'slug'         => Str::slug($this->nombre) . '-' . Str::random(4),
            'telefono'     => $this->telefono ?: null,
            'direccion'    => $this->direccion ?: null,
            'status'       => 'activo',
            'bill_date'    => now()->addDays(30)->toDateString(),
            'theme_number' => $this->theme_number,
        ]);

        $user = auth()->user();

        // Asignar al usuario creador como admin del tenant
        $tenant->users()->attach($user->id, ['role' => 'admin', 'is_active' => true]);

        // Seleccionar el nuevo tenant directamente en sesión
        session(['current_tenant_id' => $tenant->id]);

        session()->flash('success', "¡Tienda \"{$tenant->nombre}\" creada! Tienes 30 días de prueba gratuita.");

        $this->redirect(route('dashboard'), navigate: false);
    }

    public function render()
    {
        return view('livewire.admin.crear-tienda');
    }
}
