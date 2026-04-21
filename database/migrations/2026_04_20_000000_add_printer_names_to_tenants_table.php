<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Nombres de las impresoras
            $table->string('printer_nombre_ticket')->nullable()->after('printer_puerto_cocina');
            $table->string('printer_nombre_comanda')->nullable()->after('printer_nombre_ticket');

            // Configuración de auto-impresión
            $table->boolean('printer_auto_ticket')->default(true)->after('printer_nombre_comanda');
            $table->boolean('printer_auto_comanda')->default(true)->after('printer_auto_ticket');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'printer_nombre_ticket',
                'printer_nombre_comanda',
                'printer_auto_ticket',
                'printer_auto_comanda',
            ]);
        });
    }
};
