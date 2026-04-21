<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Configuración de impresión
            $table->string('printer_secret_key', 64)->nullable()->after('printer_auto_comanda')
                  ->comment('Clave AES-256 para cifrado de datos de impresión (64 chars hex)');

            $table->enum('printer_width', ['58', '80', '110'])->default('80')->after('printer_secret_key')
                  ->comment('Ancho del papel térmico en mm');

            $table->boolean('printer_logo')->default(false)->after('printer_width')
                  ->comment('Si el negocio tiene logo configurado para impresión');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'printer_secret_key',
                'printer_width',
                'printer_logo',
            ]);
        });
    }
};
