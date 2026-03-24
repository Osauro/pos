<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar tenant_id a todas las tablas del negocio
        foreach (['productos', 'turnos', 'ventas', 'movimientos'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id')->index();
            });
        }
    }

    public function down(): void
    {
        foreach (['productos', 'turnos', 'ventas', 'movimientos'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }
    }
};
