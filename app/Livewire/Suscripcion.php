<?php

namespace App\Livewire;

use App\Models\PagoSuscripcion;
use App\Models\Tenant;
use App\Traits\WithPermisos;
use App\Traits\WithSwal;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithFileUploads;

class Suscripcion extends Component
{
    use WithPermisos, WithSwal, WithFileUploads;

    public $comprobante = null;
    public string $notasPago = '';
    public bool $pagoEnviado = false;

    // Datos del negocio editables
    public string $editNombre    = '';
    public string $editTelefono  = '';
    public string $editDireccion = '';

    // Tema
    public int $themeNumber = 3;

    public function mount(): void
    {
        // Permitir acceso también cuando el tenant está inactivo (para mostrar QR y subir comprobante)
        $tenant = currentTenant();
        if ($tenant && $tenant->isActivo()) {
            $this->verificarAccesoSuscripcion();
        }

        if ($tenant) {
            $this->editNombre    = $tenant->nombre    ?? '';
            $this->editTelefono  = $tenant->telefono  ?? '';
            $this->editDireccion = $tenant->direccion ?? '';
            $this->themeNumber   = $tenant->theme_number ?? 3;
        }
    }

    public function guardarDatos(): void
    {
        $this->validate([
            'editNombre'    => 'required|string|max:100',
            'editTelefono'  => 'nullable|string|max:20',
            'editDireccion' => 'nullable|string|max:255',
        ], [
            'editNombre.required' => 'El nombre del negocio es obligatorio.',
        ]);

        $tenant = currentTenant();
        if (! $tenant) return;

        $tenant->update([
            'nombre'    => $this->editNombre,
            'telefono'  => $this->editTelefono ?: null,
            'direccion' => $this->editDireccion ?: null,
        ]);

        $this->showSuccessNotification('Datos del negocio actualizados.');
    }

    public function guardarTheme(): void
    {
        $this->validate([
            'themeNumber' => 'required|integer|in:2,3,4,5,6,7,8,9,10',
        ]);

        $tenant = currentTenant();
        if (! $tenant) return;

        $tenant->update(['theme_number' => $this->themeNumber]);

        $this->showSuccessNotification('Color del tema actualizado.');
    }

    public function enviarComprobante(): void
    {
        $this->validate([
            'comprobante' => 'required|file|max:5120|mimes:jpg,jpeg,png,pdf',
            'notasPago'   => 'nullable|string|max:500',
        ], [
            'comprobante.required' => 'Debes adjuntar el comprobante de pago.',
            'comprobante.max'      => 'El archivo no puede superar 5 MB.',
            'comprobante.mimes'    => 'Solo se aceptan imágenes JPG, PNG o PDF.',
        ]);

        $tenant = currentTenant();

        // Solo 1 pago pendiente a la vez
        $pendiente = PagoSuscripcion::where('tenant_id', $tenant->id)
            ->where('estado', 'pendiente')
            ->exists();

        if ($pendiente) {
            $this->swalInfo('Ya enviado', 'Ya tienes un comprobante pendiente de verificación. El administrador lo revisará pronto.');
            return;
        }

        $path = $this->comprobante->store('comprobantes', 'public');

        PagoSuscripcion::create([
            'tenant_id'        => $tenant->id,
            'comprobante_path' => $path,
            'monto'            => 50,
            'notas'            => $this->notasPago ?: null,
            'estado'           => 'pendiente',
        ]);

        $this->comprobante  = null;
        $this->notasPago    = '';
        $this->pagoEnviado  = true;

        $this->swalSuccess('¡Comprobante enviado!', 'El administrador verificará tu pago y activará tu cuenta.');
    }

    public function render()
    {
        $tenant = currentTenant();

        $diasRestantes  = null;
        $badgeColor     = 'secondary';
        $badgeTexto     = 'Sin plan';
        $esTrial        = false;
        $precioAnual    = 50;
        $pagoPendiente  = null;

        if ($tenant) {
            $pagoPendiente = PagoSuscripcion::where('tenant_id', $tenant->id)
                ->where('estado', 'pendiente')
                ->latest()
                ->first();
        }

        if ($tenant && $tenant->bill_date) {
            $diasRestantes = (int) \Carbon\Carbon::now()->diffInDays($tenant->bill_date, false);

            // Si bill_date está dentro del primer mes desde created_at, es prueba
            $esTrial = $tenant->bill_date->lte($tenant->created_at->addMonth()->addDay());

            if ($diasRestantes < 0) {
                $badgeColor = 'danger';
                $badgeTexto = $esTrial ? 'Prueba vencida' : 'Suscripción vencida';
            } elseif ($diasRestantes === 0) {
                $badgeColor = 'warning';
                $badgeTexto = 'Vence hoy';
            } elseif ($diasRestantes <= 7) {
                $badgeColor = 'warning';
                $badgeTexto = "Vence en {$diasRestantes} día(s)";
            } else {
                $badgeColor = 'success';
                $badgeTexto = $esTrial
                    ? "Prueba gratuita ({$diasRestantes}d)"
                    : "Activa ({$diasRestantes}d)";
            }
        }

        return view('livewire.suscripcion', compact(
            'tenant', 'diasRestantes', 'badgeColor', 'badgeTexto', 'esTrial', 'precioAnual', 'pagoPendiente'
        ));
    }
}
