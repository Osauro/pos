<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Modo de impresión: 'browser' | 'escpos' | 'network_ip'
            $table->enum('printer_modo', ['browser', 'escpos', 'network_ip'])
                  ->default('browser')
                  ->after('theme_number');

            // IP y puerto de la impresora de tickets por red LAN
            $table->string('printer_ip', 45)->nullable()->after('printer_modo');
            $table->unsignedSmallInteger('printer_puerto')->default(9100)->after('printer_ip');

            // IP y puerto de la impresora de cocina por red LAN (opcional)
            $table->string('printer_ip_cocina', 45)->nullable()->after('printer_puerto');
            $table->unsignedSmallInteger('printer_puerto_cocina')->default(9100)->after('printer_ip_cocina');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'printer_modo',
                'printer_ip',
                'printer_puerto',
                'printer_ip_cocina',
                'printer_puerto_cocina',
            ]);
        });
    }
};
