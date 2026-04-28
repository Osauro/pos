<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_user', function (Blueprint $table) {
            $table->dropColumn('wa_phone');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_user', function (Blueprint $table) {
            $table->string('wa_phone', 25)->nullable()->after('wa_api_token');
        });
    }
};
