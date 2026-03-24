<?php

namespace App\Livewire\Admin;

use App\Models\Tenant;
use App\Traits\WithSwal;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.landlord.app')]
class TenantsManager extends Component
{
    use WithPagination, WithSwal;

    public string $search = '';

    // Formulario tenant
    public ?int $tenant_id = null;
    public string $nombre   = '';
    public string $telefono = '';
    public string $direccion = '';
    public string $status   = 'activo';
    public int $theme_number = 3;
    public bool $isOpenTenant = false;

    // Formulario usuario admin del tenant
    public ?int $tenant_para_usuario = null;
    public string $u_nombre  = '';
    public string $u_celular = '';
    public string $u_tipo    = 'admin';
    public bool $isOpenUsuario = false;

    protected $rules = [
        'nombre'       => 'required|string|max:255',
        'telefono'     => 'nullable|string|max:20',
        'direccion'    => 'nullable|string|max:255',
        'status'       => 'required|in:activo,inactivo,suspendido',
        'theme_number' => 'required|integer|min:2|max:10',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $tenants = Tenant::withCount('users')
            ->when($this->search, fn($q) => $q->where('nombre', 'like', "%{$this->search}%"))
            ->orderBy('id', 'desc')
            ->paginate(12);

        return view('livewire.admin.tenants-manager', compact('tenants'));
    }

    // ── CRUD Tenants ──────────────────────────────────────────────────────────

    public function createTenant(): void
    {
        $this->resetTenantFields();
        $this->isOpenTenant = true;
    }

    public function editTenant(int $id): void
    {
        $tenant = Tenant::findOrFail($id);
        $this->tenant_id  = $id;
        $this->nombre     = $tenant->nombre;
        $this->telefono   = $tenant->telefono ?? '';
        $this->direccion  = $tenant->direccion ?? '';
        $this->status     = $tenant->status;
        $this->theme_number = $tenant->theme_number ?? 3;
        $this->isOpenTenant = true;
    }

    public function saveTenant(): void
    {
        $this->validate();

        $data = [
            'nombre'       => $this->nombre,
            'slug'         => Str::slug($this->nombre) . '-' . Str::random(4),
            'telefono'     => $this->telefono ?: null,
            'direccion'    => $this->direccion ?: null,
            'status'       => $this->status,
            'theme_number' => $this->theme_number,
        ];

        if ($this->tenant_id) {
            $tenant = Tenant::findOrFail($this->tenant_id);
            $tenant->update($data);
            $this->showSuccessNotification('Negocio actualizado.');
        } else {
            // 1 mes de prueba gratuita desde la fecha de creación
            $data['bill_date'] = now()->addMonth()->toDateString();
            Tenant::create($data);
            $this->showSuccessNotification('Negocio creado. Tiene 1 mes de prueba gratuita.');
        }

        $this->isOpenTenant = false;
        $this->resetTenantFields();
    }

    public function deleteTenant(int $id): void
    {
        $this->confirmDelete($id, 'deleteTenantConfirmed');
    }

    #[On('deleteTenantConfirmed')]
    public function deleteTenantConfirmed(int $id): void
    {
        Tenant::findOrFail($id)->delete();
        $this->showSuccessNotification('Negocio eliminado.');
    }

    public function toggleStatus(int $id): void
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->status = $tenant->status === 'activo' ? 'suspendido' : 'activo';
        $tenant->save();
        $this->showSuccessNotification('Estado actualizado.');
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    private function resetTenantFields(): void
    {
        $this->tenant_id    = null;
        $this->nombre       = '';
        $this->telefono     = '';
        $this->direccion    = '';
        $this->status       = 'activo';
        $this->theme_number = 3;
        $this->resetValidation();
    }
}
