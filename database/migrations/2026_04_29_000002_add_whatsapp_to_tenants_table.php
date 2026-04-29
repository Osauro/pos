<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('wa_instance_id')->nullable()->after('horario_fin');
            $table->string('wa_api_token')->nullable()->after('wa_instance_id');
            $table->boolean('wa_notify_ventas')->default(false)->after('wa_api_token');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['wa_instance_id', 'wa_api_token', 'wa_notify_ventas']);
        });
    }
};
