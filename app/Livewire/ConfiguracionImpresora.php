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

    public string $printer_modo         = 'browser';
    public string $printer_ip           = '';
    public int    $printer_puerto       = 9100;
    public string $printer_ip_cocina    = '';
    public int    $printer_puerto_cocina = 9100;

    // Estado del test de conexión
    public ?bool  $testTicketOk  = null;
    public ?bool  $testCocinaOk  = null;
    public string $testMsg       = '';

    public function mount(): void
    {
        if (!$this->esAdmin()) {
            abort(403);
        }

        $tenant = TenantHelper::current();
        if (!$tenant) abort(404);

        $this->printer_modo          = $tenant->printer_modo          ?? 'browser';
        $this->printer_ip            = $tenant->printer_ip            ?? '';
        $this->printer_puerto        = $tenant->printer_puerto        ?? 9100;
        $this->printer_ip_cocina     = $tenant->printer_ip_cocina     ?? '';
        $this->printer_puerto_cocina = $tenant->printer_puerto_cocina ?? 9100;
    }

    public function guardar(): void
    {
        $this->validate([
            'printer_modo'          => 'required|in:browser,escpos,network_ip',
            'printer_ip'            => [
                'nullable',
                'string',
                'max:45',
                function ($attr, $val, $fail) {
                    if ($this->printer_modo === 'network_ip' && empty(trim($val))) {
                        $fail('La IP de la impresora es obligatoria en modo Red LAN.');
                    }
                    if (!empty($val) && !filter_var($val, FILTER_VALIDATE_IP)) {
                        $fail('La IP de la impresora no es válida.');
                    }
                },
            ],
            'printer_puerto'        => 'required|integer|min:1|max:65535',
            'printer_ip_cocina'     => [
                'nullable',
                'string',
                'max:45',
                function ($attr, $val, $fail) {
                    if (!empty($val) && !filter_var($val, FILTER_VALIDATE_IP)) {
                        $fail('La IP de la impresora de cocina no es válida.');
                    }
                },
            ],
            'printer_puerto_cocina' => 'required|integer|min:1|max:65535',
        ]);

        $tenant = TenantHelper::current();
        if (!$tenant) return;

        $tenant->update([
            'printer_modo'          => $this->printer_modo,
            'printer_ip'            => trim($this->printer_ip) ?: null,
            'printer_puerto'        => $this->printer_puerto,
            'printer_ip_cocina'     => trim($this->printer_ip_cocina) ?: null,
            'printer_puerto_cocina' => $this->printer_puerto_cocina,
        ]);

        $this->testTicketOk = null;
        $this->testCocinaOk = null;
        $this->testMsg      = '';

        $this->showSuccessNotification('Configuración guardada correctamente.');
    }

    /**
     * Prueba la conexión TCP a la impresora de tickets.
     * Envía un feed de 3 líneas para verificar que responde.
     */
    public function testConexionTicket(): void
    {
        $ip    = trim($this->printer_ip);
        $port  = (int) $this->printer_puerto;

        [$ok, $msg] = $this->testSocket($ip, $port);
        $this->testTicketOk = $ok;
        $this->testMsg      = $msg;

        if ($ok) {
            $this->showSuccessNotification("Impresora ticket ({$ip}:{$port}) responde OK.");
        } else {
            $this->showErrorNotification("No se pudo conectar a {$ip}:{$port}. {$msg}");
        }
    }

    /**
     * Prueba la conexión TCP a la impresora de cocina.
     */
    public function testConexionCocina(): void
    {
        $ip   = trim($this->printer_ip_cocina);
        $port = (int) $this->printer_puerto_cocina;

        [$ok, $msg] = $this->testSocket($ip, $port);
        $this->testCocinaOk = $ok;

        if ($ok) {
            $this->showSuccessNotification("Impresora cocina ({$ip}:{$port}) responde OK.");
        } else {
            $this->showErrorNotification("No se pudo conectar a {$ip}:{$port}. {$msg}");
        }
    }

    private function testSocket(string $ip, int $port): array
    {
        if (empty($ip)) {
            return [false, 'Ingresa una IP antes de probar.'];
        }
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return [false, 'IP no válida.'];
        }

        $socket = @fsockopen($ip, $port, $errno, $errstr, 5);
        if ($socket === false) {
            return [false, "{$errstr} (error {$errno})"];
        }

        // Enviar un pequeño feed para que la impresora avance el papel
        fwrite($socket, "\x1B\x40\n\n\n");
        fclose($socket);

        return [true, 'Conexión exitosa.'];
    }

    public function render()
    {
        return view('livewire.configuracion-impresora');
    }
}
