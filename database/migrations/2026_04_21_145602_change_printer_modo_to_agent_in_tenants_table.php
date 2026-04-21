<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cambiar ENUM a string para admitir el valor 'agent'
        \DB::statement("ALTER TABLE tenants MODIFY printer_modo VARCHAR(20) NOT NULL DEFAULT 'agent'");
        // Migrar todos los tenants existentes al modo agente HTTP
        \DB::table('tenants')->update(['printer_modo' => 'agent']);
    }

    public function down(): void
    {
        \DB::statement("ALTER TABLE tenants MODIFY printer_modo ENUM('browser','escpos','network_ip','agent') NOT NULL DEFAULT 'escpos'");
    }
};
