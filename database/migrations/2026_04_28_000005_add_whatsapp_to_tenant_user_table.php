<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_user', function (Blueprint $table) {
            $table->string('wa_instance_id')->nullable()->after('qr_imagen');
            $table->string('wa_api_token')->nullable()->after('wa_instance_id');
            $table->string('wa_phone', 25)->nullable()->after('wa_api_token');
            $table->boolean('wa_notify_ventas')->default(false)->after('wa_phone');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_user', function (Blueprint $table) {
            $table->dropColumn(['wa_instance_id', 'wa_api_token', 'wa_phone', 'wa_notify_ventas']);
        });
    }
};
