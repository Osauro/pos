<?php

namespace App\Livewire\Admin;

use App\Models\Tenant;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.theme.app')]
class CrearTienda extends Component
{
    public string $nombreTenant    = '';
    public string $celularTenant   = '';
    public string $direccionTenant = '';
    public int    $colorTenant     = 3;

    protected array $rules = [
        'nombreTenant'    => 'required|min:2|max:100',
        'celularTenant'   => 'nullable|max:20',
        'direccionTenant' => 'nullable|max:200',
        'colorTenant'     => 'required|integer|min:2|max:10',
    ];

    protected array $messages = [
        'nombreTenant.required' => 'El nombre de la tienda es obligatorio.',
        'nombreTenant.min'      => 'El nombre debe tener al menos 2 caracteres.',
        'nombreTenant.max'      => 'El nombre no puede superar 100 caracteres.',
    ];

    public function seleccionarColor(int $num): void
    {
        $this->colorTenant = $num;
    }

    public function activar(): void
    {
        $this->validate();

        $tenant = Tenant::create([
            'nombre'       => $this->nombreTenant,
            'slug'         => Str::slug($this->nombreTenant) . '-' . Str::random(4),
            'telefono'     => $this->celularTenant ?: null,
            'direccion'    => $this->direccionTenant ?: null,
            'status'       => 'activo',
            'bill_date'    => now()->addDays(30)->toDateString(),
            'theme_number' => $this->colorTenant,
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
