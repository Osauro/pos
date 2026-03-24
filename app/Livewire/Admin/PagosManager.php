<?php

namespace App\Livewire\Admin;

use App\Models\PagoSuscripcion;
use App\Models\Tenant;
use App\Traits\WithSwal;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.landlord.app')]
class PagosManager extends Component
{
    use WithPagination, WithSwal;

    public string $search       = '';
    public bool $soloPendientes = true;

    // Modal
    public ?int $pagoId    = null;
    public bool $isOpenPago = false;
    public string $notasRechazo = '';

    protected $queryString = ['search', 'soloPendientes'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

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

    public function render()
    {
        $pagos = PagoSuscripcion::with('tenant', 'verificadoPor')
            ->when($this->soloPendientes, fn($q) => $q->where('estado', 'pendiente'))
            ->when($this->search, fn($q) => $q->whereHas('tenant', fn($q2) =>
                $q2->where('nombre', 'like', "%{$this->search}%")
            ))
            ->latest()
            ->paginate(15);

        $totalPendientes = PagoSuscripcion::where('estado', 'pendiente')->count();

        return view('livewire.admin.pagos-manager', compact('pagos', 'totalPendientes'));
    }
}
