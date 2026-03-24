<?php

namespace App\Livewire\Admin;

use App\Models\Tenant;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.crear-tienda')]
class CrearTienda extends Component
{
    public string $nombre       = '';
    public string $telefono     = '';
    public string $direccion    = '';
    public int    $theme_number = 3;

    protected array $rules = [
        'nombre'       => 'required|min:2|max:100',
        'telefono'     => 'nullable|max:20',
        'direccion'    => 'nullable|max:200',
        'theme_number' => 'required|integer|min:2|max:10',
    ];

    protected array $messages = [
        'nombre.required' => 'El nombre de la tienda es obligatorio.',
        'nombre.min'      => 'El nombre debe tener al menos 2 caracteres.',
    ];

    public function saveTenant(): void
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

        $tenant->users()->attach(auth()->id(), ['role' => 'admin', 'is_active' => true]);

        session(['current_tenant_id' => $tenant->id]);

        $this->redirect(route('dashboard'), navigate: false);
    }

    public function render()
    {
        return view('livewire.admin.crear-tienda');
    }
}
