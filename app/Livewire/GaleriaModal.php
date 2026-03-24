<?php

namespace App\Livewire;

use App\Models\GaleriaImagen;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Component;
use Livewire\WithFileUploads;

class GaleriaModal extends Component
{
    use WithFileUploads;

    public bool $mostrar = false;
    public string $busqueda = '';
    public $nuevaImagen;

    protected $listeners = ['abrir-galeria' => 'abrir'];

    public function abrir(string $busqueda = ''): void
    {
        $this->mostrar = true;
        $this->busqueda = $busqueda;
        $this->nuevaImagen = null;
        $this->resetErrorBag();
    }

    public function cerrar(): void
    {
        $this->mostrar = false;
        $this->nuevaImagen = null;
    }

    public function seleccionar(int $id): void
    {
        $img = GaleriaImagen::findOrFail($id);
        $this->dispatch('imagen-seleccionada', id: $id, url: $img->photo_url, path: $img->url);
        $this->cerrar();
    }

    public function updatedNuevaImagen(): void
    {
        if ($this->nuevaImagen) {
            $this->subirImagen();
        }
    }

    public function subirImagen(): void
    {
        $this->validate([
            'nuevaImagen' => 'required|image|max:10240',
        ]);

        $filename = time() . '_' . uniqid() . '.jpg';
        $path = 'galeria/' . $filename;

        $processed = Image::read($this->nuevaImagen->getRealPath())
            ->cover(512, 512)
            ->toJpeg(90);

        Storage::disk('public')->put($path, (string) $processed);

        $img = GaleriaImagen::create([
            'url'        => $path,
            'nombre'     => null,
            'tags'       => [],
            'veces_usado'=> 0,
            'subido_por' => Auth::id(),
        ]);

        $this->dispatch('imagen-seleccionada', id: $img->id, url: asset('storage/' . $path), path: $path);
        $this->cerrar();
    }

    public function getImagenesProperty()
    {
        return GaleriaImagen::when($this->busqueda, function ($q) {
                $q->where('nombre', 'like', '%' . $this->busqueda . '%')
                  ->orWhere('tags', 'like', '%' . $this->busqueda . '%');
            })
            ->orderByDesc('veces_usado')
            ->orderByDesc('updated_at')
            ->limit(60)
            ->get();
    }

    public function render()
    {
        return view('livewire.galeria-modal', [
            'imagenes' => $this->imagenes,
        ]);
    }
}
