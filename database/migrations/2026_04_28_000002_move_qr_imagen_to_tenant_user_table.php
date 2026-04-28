<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar qr_imagen al pivot tenant_user (por admin)
        Schema::table('tenant_user', function (Blueprint $table) {
            $table->string('qr_imagen')->nullable()->after('is_active');
        });

        // Quitar qr_imagen del tenant (ya no se usa ahí)
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('qr_imagen');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_user', function (Blueprint $table) {
            $table->dropColumn('qr_imagen');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->string('qr_imagen')->nullable()->after('printer_show_nombre');
        });
    }
};
