<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique()->nullable(); // subdomain/identifier
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->string('logo')->nullable();
            $table->enum('status', ['activo', 'inactivo', 'suspendido'])->default('activo');
            $table->date('bill_date')->nullable();        // fecha vencimiento suscripción
            $table->timestamps();
        });

        // Agregar FK de tenant_id en tablas del negocio
        foreach (['productos', 'turnos', 'ventas', 'movimientos'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                $table->foreign('tenant_id')
                    ->references('id')->on('tenants')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['productos', 'turnos', 'ventas', 'movimientos'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
            });
        }

        Schema::dropIfExists('tenants');
    }
};
