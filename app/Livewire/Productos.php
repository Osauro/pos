<?php

namespace App\Livewire;

use App\Models\GaleriaImagen;
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

    // Galería / Imagen
    public $imagen_preview_url;
    public $galeria_id_seleccionado;
    public $producto_actual;

    protected $listeners = [
        'deleteProduct',
        'imagen-seleccionada' => 'imagenSeleccionada',
    ];

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

    public function abrirGaleria(): void
    {
        $this->dispatch('abrir-galeria', busqueda: trim($this->nombre ?? ''));
    }

    public function imagenSeleccionada(int $id, string $url, string $path): void
    {
        $this->galeria_id_seleccionado = $id;
        $this->imagen = $path;
        $this->imagen_preview_url = $url;
    }

    public function store()
    {
        $this->validate();

        $producto = Producto::create([
            'nombre' => $this->nombre,
            'imagen' => $this->imagen,
            'precio' => $this->precio,
            'tipo' => $this->tipo,
            'estado' => true,
        ]);

        if ($this->galeria_id_seleccionado) {
            $this->actualizarGaleria($this->galeria_id_seleccionado);
        }

        $this->showSuccessNotification('Producto creado exitosamente');
        $this->closeModal();
    }

    public function edit($id)
    {
        $producto = Producto::findOrFail($id);
        $this->producto_id = $id;
        $this->producto_actual = $producto;
        $this->nombre = $producto->nombre;
        $this->imagen = null;
        $this->imagen_preview_url = null;
        $this->galeria_id_seleccionado = null;
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

        if ($this->galeria_id_seleccionado && $this->imagen) {
            $producto->imagen = $this->imagen;
            $this->actualizarGaleria($this->galeria_id_seleccionado);
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

    private function actualizarGaleria(int $galeriaImagenId): void
    {
        $galeria = GaleriaImagen::find($galeriaImagenId);
        if (!$galeria) return;

        $galeria->increment('veces_usado');
        $galeria->mergeTags([trim($this->nombre)]);

        if (!$galeria->nombre) {
            $galeria->nombre = trim($this->nombre);
        }

        $galeria->save();
    }

    private function resetInputFields()
    {
        $this->producto_id = null;
        $this->producto_actual = null;
        $this->nombre = '';
        $this->imagen = null;
        $this->new_imagen = null;
        $this->imagen_preview_url = null;
        $this->galeria_id_seleccionado = null;
        $this->precio = '';
        $this->tipo = '';
        $this->resetValidation();
    }
}
