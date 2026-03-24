<?php

namespace App\Livewire;

use App\Models\User;
use App\Traits\WithSwal;
use App\Traits\WithPermisos;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;

class Usuarios extends Component
{
    use WithPagination, WithSwal, WithPermisos;

    public $usuario_id, $nombre, $celular, $pin, $tipo = 'user';
    public $isOpen = false;
    public $perPage = 10;
    public ?int $usuarioExistenteId = null;
    public bool $confirmarAsociacion = false;
    public string $nombreExistente = '';

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'celular' => 'required|digits:8',
        'tipo' => 'required|in:admin,operador'
    ];

    private function generarPin(): string
    {
        return str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function mount()
    {
        $this->verificarAccesoUsuarios();
        $this->perPage = request()->cookie('paginateUsuarios', 10);
    }

    public function render()
    {
        $usuarios = User::whereHas('tenants', function ($q) {
                $q->where('tenants.id', currentTenantId())
                  ->where('tenant_user.is_active', true);
            })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        return view('livewire.usuarios', compact('usuarios'))
            ->with('puedeEliminar', $this->puedeEliminar());
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isOpen = true;
    }

    public function store()
    {
        $this->validate();

        // Verificar si ya existe un usuario con ese celular
        $existente = User::where('celular', $this->celular)->first();

        if ($existente) {
            // Verificar que no esté ya en este tenant
            $yaEnTenant = $existente->tenants()
                ->where('tenants.id', currentTenantId())
                ->exists();

            if ($yaEnTenant) {
                $this->addError('celular', 'Este celular ya está registrado en esta tienda.');
                return;
            }

            // Guardar para confirmar asociación
            $tipoSeleccionado = $this->tipo;
            $existenteId      = $existente->id;
            $this->closeModal();
            $this->usuarioExistenteId  = $existenteId;
            $this->tipo                = $tipoSeleccionado;
            $this->nombreExistente     = $existente->nombre;
            $this->confirmarAsociacion = true;
            return;
        }

        $pin = $this->generarPin();

        $usuario = User::create([
            'nombre'  => $this->nombre,
            'celular' => $this->celular,
            'pin'     => Hash::make($pin),
        ]);

        $usuario->tenants()->attach(currentTenantId(), [
            'role'      => $this->tipo,
            'is_active' => true,
        ]);

        $this->closeModal();
        $this->dispatch('swal:pin', nombre: $usuario->nombre, pin: $pin);
    }

    #[On('asociarUsuarioConfirmado')]
    public function asociarUsuarioConfirmado(): void
    {
        if (! $this->usuarioExistenteId) return;

        $usuario = User::findOrFail($this->usuarioExistenteId);
        $usuario->tenants()->attach(currentTenantId(), [
            'role'      => $this->tipo,
            'is_active' => true,
        ]);

        $this->usuarioExistenteId  = null;
        $this->confirmarAsociacion = false;
        $this->nombreExistente     = '';
        $this->showSuccessNotification("Usuario \"{$usuario->nombre}\" asociado a esta tienda.");
    }

    public function cancelarAsociacion(): void
    {
        $this->usuarioExistenteId  = null;
        $this->confirmarAsociacion = false;
        $this->nombreExistente     = '';
        $this->tipo                = 'operador';
    }

    public function edit($id)
    {
        $usuario = User::findOrFail($id);
        $this->usuario_id = $id;
        $this->nombre = $usuario->nombre;
        $this->celular = $usuario->celular;
        // Obtener el rol del usuario en el tenant actual
        $pivot = $usuario->tenants()->where('tenants.id', currentTenantId())->first();
        $this->tipo = $pivot?->pivot->role ?? 'operador';
        $this->pin = '';

        $this->isOpen = true;
    }

    public function update()
    {
        $this->validate();

        $pin = $this->generarPin();

        $usuario = User::findOrFail($this->usuario_id);
        $usuario->nombre  = $this->nombre;
        $usuario->celular = $this->celular;
        $usuario->pin     = Hash::make($pin);
        $usuario->save();

        // Actualizar rol en el tenant
        $usuario->tenants()->syncWithoutDetaching([
            currentTenantId() => ['role' => $this->tipo],
        ]);

        $this->closeModal();
        $this->dispatch('swal:pin', nombre: $usuario->nombre, pin: $pin);
    }

    public function resetPin($id)
    {
        $usuario = User::findOrFail($id);
        $pin = $this->generarPin();
        $usuario->pin = Hash::make($pin);
        $usuario->save();
        $this->dispatch('swal:pin', nombre: $usuario->nombre, pin: $pin);
    }

    public function delete($id)
    {
        $this->confirmDelete($id, 'deleteConfirmed');
    }

    #[On('deleteConfirmed')]
    public function deleteConfirmed($id)
    {
        User::find($id)->delete();
        $this->showSuccessNotification('Usuario eliminado exitosamente');
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->usuario_id = null;
        $this->nombre = '';
        $this->celular = '';
        $this->pin = '';
        $this->tipo = 'operador';
        $this->resetValidation();
    }
}
