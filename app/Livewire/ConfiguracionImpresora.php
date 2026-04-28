<?php

namespace App\Livewire;

use App\Helpers\TenantHelper;
use App\Models\User;
use App\Services\GreenApiService;
use App\Traits\WithPermisos;
use App\Traits\WithSwal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.theme.app')]
class ConfiguracionImpresora extends Component
{
    use WithPermisos, WithSwal, WithFileUploads;

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

    // QR de pago online
    #[Validate('nullable|image|max:2048')]
    public $qr_imagen_file = null;
    public ?string $qr_imagen_actual = null;

    // WhatsApp — Green API
    public string $wa_instance_id  = '';
    public string $wa_api_token    = '';
    public string $wa_phone        = '';
    public bool   $wa_notify_ventas = false;

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

        // QR propio del admin en este tenant (pivot)
        $pivot = User::find(Auth::id())->tenants()
            ->wherePivot('tenant_id', $tenant->id)
            ->first()?->pivot;
        $this->qr_imagen_actual = $pivot?->qr_imagen ?? null;

        // WhatsApp
        $this->wa_instance_id   = $pivot?->wa_instance_id  ?? '';
        $this->wa_api_token     = $pivot?->wa_api_token    ?? '';
        $this->wa_phone         = $pivot?->wa_phone        ?? '';
        $this->wa_notify_ventas = (bool) ($pivot?->wa_notify_ventas ?? false);
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

    public function guardarQR(): void
    {
        $this->validateOnly('qr_imagen_file', [
            'qr_imagen_file' => 'nullable|image|max:2048',
        ]);

        $tenantId = TenantHelper::currentId();
        if (!$tenantId) return;

        if ($this->qr_imagen_file) {
            // Borrar imagen anterior si existe
            if ($this->qr_imagen_actual) {
                Storage::disk('public')->delete($this->qr_imagen_actual);
            }

            $path = $this->qr_imagen_file->store('qr', 'public');

            User::find(Auth::id())->tenants()->updateExistingPivot($tenantId, ['qr_imagen' => $path]);

            $this->qr_imagen_actual = $path;
            $this->qr_imagen_file   = null;
        }

        $this->showSuccessNotification('Imagen QR guardada correctamente.');
    }

    public function eliminarQR(): void
    {
        $tenantId = TenantHelper::currentId();
        if (!$tenantId) return;

        if ($this->qr_imagen_actual) {
            Storage::disk('public')->delete($this->qr_imagen_actual);
        }

        User::find(Auth::id())->tenants()->updateExistingPivot($tenantId, ['qr_imagen' => null]);

        $this->qr_imagen_actual = null;
        $this->qr_imagen_file   = null;

        $this->showSuccessNotification('Imagen QR eliminada.');
    }

    /**
     * Genera una clave aleatoria de 64 caracteres hexadecimales (32 bytes).
     */
    public function generarClave(): void
    {
        $this->printer_secret_key = bin2hex(random_bytes(32));
        $this->showSuccessNotification('Clave generada correctamente. No olvides guardar la configuración.');
    }

    /**
     * Elimina todos los datos del tenant excepto productos y usuarios.
     * Borra: ventas, venta_items, turnos, movimientos
     * Resetea: total_vendido en productos
     */
    public function resetTenant(): void
    {
        if (!$this->esAdmin()) {
            abort(403);
        }

        $tenantId = TenantHelper::currentId();
        if (!$tenantId) return;

        DB::transaction(function () use ($tenantId) {
            // venta_items no tiene tenant_id, se borra via ventas
            $ventaIds = DB::table('ventas')->where('tenant_id', $tenantId)->pluck('id');
            if ($ventaIds->isNotEmpty()) {
                DB::table('venta_items')->whereIn('venta_id', $ventaIds)->delete();
            }

            DB::table('ventas')->where('tenant_id', $tenantId)->delete();
            DB::table('turnos')->where('tenant_id', $tenantId)->delete();
            DB::table('movimientos')->where('tenant_id', $tenantId)->delete();

            // Resetear contador de ventas en productos
            DB::table('productos')->where('tenant_id', $tenantId)->update(['total_vendido' => 0]);
        });

        $this->showSuccessNotification('Datos del tenant reseteados correctamente.');
    }

    public function resetDia(): void
    {
        if (!$this->esAdmin()) {
            abort(403);
        }

        $tenantId = TenantHelper::currentId();
        if (!$tenantId) return;

        $hoy = Carbon::today()->toDateString();

        // Buscar el turno activo hoy
        $turno = DB::table('turnos')
            ->where('tenant_id', $tenantId)
            ->where('fecha_inicio', '<=', $hoy)
            ->where('fecha_fin', '>=', $hoy)
            ->first();

        if (!$turno) {
            $this->swalWarning('Sin turno activo', 'No hay ningún turno activo hoy. No hay datos que borrar.');
            return;
        }

        DB::transaction(function () use ($tenantId, $turno, $hoy) {
            // Ventas del turno activo (usando fecha o turno_id si existe)
            $ventaIds = DB::table('ventas')
                ->where('tenant_id', $tenantId)
                ->where('turno_id', $turno->id)
                ->pluck('id');

            if ($ventaIds->isNotEmpty()) {
                DB::table('venta_items')->whereIn('venta_id', $ventaIds)->delete();
                DB::table('ventas')->whereIn('id', $ventaIds)->delete();
            }

            // Movimientos del turno activo
            DB::table('movimientos')
                ->where('turno_id', $turno->id)
                ->delete();
        });

        $this->showSuccessNotification('Ventas y movimientos del día eliminados correctamente.');
    }

    public function guardarWhatsapp(): void
    {
        $this->validate([
            'wa_instance_id'   => 'nullable|string|max:100',
            'wa_api_token'     => 'nullable|string|max:100',
            'wa_phone'         => ['nullable', 'string', 'max:25', 'regex:/^\d+$/'],
            'wa_notify_ventas' => 'boolean',
        ]);

        $tenantId = TenantHelper::currentId();
        if (!$tenantId) return;

        User::find(Auth::id())->tenants()->updateExistingPivot($tenantId, [
            'wa_instance_id'   => trim($this->wa_instance_id)  ?: null,
            'wa_api_token'     => trim($this->wa_api_token)    ?: null,
            'wa_phone'         => trim($this->wa_phone)        ?: null,
            'wa_notify_ventas' => $this->wa_notify_ventas,
        ]);

        $this->showSuccessNotification('Configuración de WhatsApp guardada.');
    }

    public function probarWhatsapp(): void
    {
        if (empty($this->wa_instance_id) || empty($this->wa_api_token) || empty($this->wa_phone)) {
            $this->showErrorNotification('Completa los campos de instancia, token y teléfono antes de probar.');
            return;
        }

        $ok = (new GreenApiService())->sendMessage(
            $this->wa_instance_id,
            $this->wa_api_token,
            $this->wa_phone,
            "✅ *TPV* — Conexión WhatsApp funcionando correctamente."
        );

        if ($ok) {
            $this->showSuccessNotification('Mensaje de prueba enviado correctamente.');
        } else {
            $this->showErrorNotification('No se pudo enviar el mensaje. Verifica la instancia y el token.');
        }
    }

    public function render()
    {
        return view('livewire.configuracion-impresora');
    }
}
