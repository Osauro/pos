<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_suscripcion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('comprobante_path')->nullable();   // ruta del archivo subido
            $table->decimal('monto', 8, 2)->default(50);
            $table->text('notas')->nullable();                // mensaje del tenante
            $table->enum('estado', ['pendiente', 'verificado', 'rechazado'])->default('pendiente');
            $table->text('notas_verificacion')->nullable();   // respuesta del landlord
            $table->foreignId('verificado_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verificado_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_suscripcion');
    }
};
