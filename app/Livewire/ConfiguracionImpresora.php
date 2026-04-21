<?php

namespace App\Livewire;

use App\Helpers\TenantHelper;
use App\Traits\WithPermisos;
use App\Traits\WithSwal;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.theme.app')]
class ConfiguracionImpresora extends Component
{
    use WithPermisos, WithSwal;

    // Modo único: agente HTTP local (http://localhost:9876)
    private string $printer_modo = 'agent';

    // Nombres de impresoras
    public string $printer_nombre_ticket  = '';
    public string $printer_nombre_comanda = '';

    // Auto-impresión
    public bool   $printer_auto_ticket  = true;
    public bool   $printer_auto_comanda = true;

    // Configuración de impresión
    public string $printer_secret_key    = '';
    public string $printer_width         = '80';
    public bool   $printer_logo          = false;
    public bool   $printer_show_nombre   = true;



    public function mount(): void
    {
        if (!$this->esAdmin()) {
            abort(403);
        }

        $tenant = TenantHelper::current();
        if (!$tenant) abort(404);

        $this->printer_modo          = 'agent';

        $this->printer_nombre_ticket  = $tenant->printer_nombre_ticket  ?? '';
        $this->printer_nombre_comanda = $tenant->printer_nombre_comanda ?? '';
        $this->printer_auto_ticket    = $tenant->printer_auto_ticket    ?? true;
        $this->printer_auto_comanda   = $tenant->printer_auto_comanda   ?? true;

        $this->printer_secret_key   = $tenant->printer_secret_key   ?? '';
        $this->printer_width        = $tenant->printer_width        ?? '80';
        $this->printer_logo         = $tenant->printer_logo         ?? false;
        $this->printer_show_nombre  = $tenant->printer_show_nombre  ?? true;
    }

    public function guardar(): void
    {
        $this->validate([
            'printer_nombre_ticket'  => 'nullable|string|max:255',
            'printer_nombre_comanda' => 'nullable|string|max:255',
            'printer_auto_ticket'    => 'boolean',
            'printer_auto_comanda'   => 'boolean',
            'printer_secret_key'     => 'nullable|string|size:64',
            'printer_width'          => 'required|in:58,80,110',
            'printer_logo'           => 'boolean',
            'printer_show_nombre'    => 'boolean',
        ]);

        $tenant = TenantHelper::current();
        if (!$tenant) return;

        $tenant->update([
            'printer_modo'           => 'agent',
            'printer_nombre_ticket'  => trim($this->printer_nombre_ticket) ?: null,
            'printer_nombre_comanda' => trim($this->printer_nombre_comanda) ?: null,
            'printer_auto_ticket'    => $this->printer_auto_ticket,
            'printer_auto_comanda'   => $this->printer_auto_comanda,
            'printer_secret_key'     => trim($this->printer_secret_key) ?: null,
            'printer_width'          => $this->printer_width,
            'printer_logo'           => $this->printer_logo,
            'printer_show_nombre'    => $this->printer_show_nombre,
        ]);

        $this->showSuccessNotification('Configuración guardada correctamente.');
    }

    /**
     * Genera una clave aleatoria de 64 caracteres hexadecimales (32 bytes).
     */
    public function generarClave(): void
    {
        $this->printer_secret_key = bin2hex(random_bytes(32));
        $this->showSuccessNotification('Clave generada correctamente. No olvides guardar la configuración.');
    }

    public function render()
    {
        return view('livewire.configuracion-impresora');
    }
}
