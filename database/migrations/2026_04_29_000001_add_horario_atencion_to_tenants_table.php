<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->time('horario_inicio')->nullable()->default(null)->after('printer_show_nombre')
                  ->comment('Hora de inicio del día comercial, ej: 13:00');
            $table->time('horario_fin')->nullable()->default(null)->after('horario_inicio')
                  ->comment('Hora de cierre del día comercial, ej: 02:00 (puede ser del día siguiente)');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['horario_inicio', 'horario_fin']);
        });
    }
};
