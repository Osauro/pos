<?php

namespace App\Livewire\Admin;

use App\Models\PagoSuscripcion;
use App\Models\Tenant;
use App\Traits\WithSwal;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.landlord.app')]
class PagosManager extends Component
{
    use WithPagination, WithSwal, WithFileUploads;

    public bool $soloPendientes = true;

    // Modal ver comprobante
    public ?int $pagoId     = null;
    public bool $isOpenPago  = false;
    public string $notasRechazo = '';

    // Modal QR
    public bool $isOpenQr = false;
    public $nuevoQr       = null;

    protected $queryString = ['soloPendientes'];

    public function updatingSoloPendientes(): void
    {
        $this->resetPage();
    }

    public function verPago(int $id): void
    {
        $this->pagoId       = $id;
        $this->notasRechazo = '';
        $this->isOpenPago   = true;
    }

    public function confirmarPago(int $id): void
    {
        $pago   = PagoSuscripcion::with('tenant')->findOrFail($id);
        $tenant = $pago->tenant;

        $base          = ($tenant->bill_date && $tenant->bill_date->isFuture()) ? $tenant->bill_date : now();
        $tenant->bill_date = $base->addYear();
        $tenant->status    = 'activo';
        $tenant->save();

        $pago->update([
            'estado'       => 'verificado',
            'verificado_by' => auth()->id(),
            'verificado_at' => now(),
        ]);

        $this->isOpenPago = false;
        $this->pagoId     = null;
        $this->showSuccessNotification("Pago confirmado. {$tenant->nombre} reactivado por 1 año.");
    }

    public function rechazarPago(int $id): void
    {
        $this->validate(['notasRechazo' => 'nullable|string|max:500']);

        $pago = PagoSuscripcion::findOrFail($id);
        $pago->update([
            'estado'             => 'rechazado',
            'notas_verificacion' => $this->notasRechazo ?: 'Comprobante rechazado.',
            'verificado_by'      => auth()->id(),
            'verificado_at'      => now(),
        ]);

        $this->isOpenPago   = false;
        $this->pagoId       = null;
        $this->notasRechazo = '';
        $this->showSuccessNotification('Pago rechazado.');
    }

    public function abrirModalQr(): void
    {
        $this->nuevoQr = null;
        $this->isOpenQr = true;
    }

    public function cerrarModalQr(): void
    {
        $this->isOpenQr = false;
        $this->nuevoQr  = null;
        $this->resetValidation('nuevoQr');
    }

    public function subirQr(): void
    {
        $this->validate([
            'nuevoQr' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'nuevoQr.required' => 'Selecciona una imagen.',
            'nuevoQr.image'    => 'El archivo debe ser una imagen.',
            'nuevoQr.mimes'    => 'Solo JPG o PNG.',
            'nuevoQr.max'      => 'Máximo 2 MB.',
        ]);

        Storage::disk('public')->putFileAs('', $this->nuevoQr, 'qr_pago.jpg');

        $this->cerrarModalQr();
        $this->showSuccessNotification('QR de pago actualizado.');
    }

    public function render()
    {
        $pagos = PagoSuscripcion::with('tenant', 'verificadoPor')
            ->when($this->soloPendientes, fn($q) => $q->where('estado', 'pendiente'))
            ->latest()
            ->paginate(15);

        $totalPendientes = PagoSuscripcion::where('estado', 'pendiente')->count();
        $qrUrl = Storage::disk('public')->exists('qr_pago.jpg')
            ? asset('storage/qr_pago.jpg') . '?v=' . filemtime(storage_path('app/public/qr_pago.jpg'))
            : null;

        return view('livewire.admin.pagos-manager', compact('pagos', 'totalPendientes', 'qrUrl'));
    }
}
