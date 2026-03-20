<?php

namespace App\Livewire;

use App\Models\Producto;
use App\Traits\WithSwal;
use App\Traits\WithPermisos;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Productos extends Component
{
    use WithPagination, WithSwal, WithFileUploads, WithPermisos;

    public $producto_id, $nombre, $imagen, $precio, $tipo;
    public $isOpen = false;
    public $new_imagen;
    public $perPage = 10;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'precio' => 'required|numeric|min:0',
        'tipo' => 'required|in:Platos,Refrescos,Porciones'
    ];

    public function mount()
    {
        $this->verificarAccesoProductos();
        $this->perPage = request()->cookie('paginateProductos', 10);
    }

    public function render()
    {
        $productos = Producto::orderBy('id', 'desc')
            ->paginate($this->perPage);

        return view('livewire.productos', compact('productos'))
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

        $imagePath = null;
        if ($this->new_imagen) {
            $imagePath = $this->new_imagen->store('productos', 'public');
        }

        Producto::create([
            'nombre' => $this->nombre,
            'imagen' => $imagePath,
            'precio' => $this->precio,
            'tipo' => $this->tipo,
            'estado' => true // Por defecto activo
        ]);

        $this->showSuccessNotification('Producto creado exitosamente');
        $this->closeModal();
    }

    public function edit($id)
    {
        $producto = Producto::findOrFail($id);
        $this->producto_id = $id;
        $this->nombre = $producto->nombre;
        $this->imagen = $producto->imagen;
        $this->precio = $producto->precio;
        $this->tipo = $producto->tipo;

        $this->isOpen = true;
    }

    public function update()
    {
        $this->validate();

        $producto = Producto::find($this->producto_id);
        $producto->nombre = $this->nombre;
        $producto->precio = $this->precio;
        $producto->tipo = $this->tipo;

        if ($this->new_imagen) {
            $producto->imagen = $this->new_imagen->store('productos', 'public');
        }

        $producto->save();

        $this->showSuccessNotification('Producto actualizado exitosamente');
        $this->closeModal();
    }

    public function delete($id)
    {
        $this->confirmDelete($id, 'deleteConfirmed');
    }

    public function toggleEstado($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->estado = !$producto->estado;
        $producto->save();

        $estadoTexto = $producto->estado ? 'activado' : 'desactivado';
        $this->showSuccessNotification("Producto {$estadoTexto} exitosamente");
    }

    #[On('deleteConfirmed')]
    public function deleteConfirmed($id)
    {
        Producto::find($id)->delete();
        $this->showSuccessNotification('Producto eliminado exitosamente');
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->producto_id = null;
        $this->nombre = '';
        $this->imagen = null;
        $this->new_imagen = null;
        $this->precio = '';
        $this->tipo = '';
        $this->resetValidation();
    }
}
