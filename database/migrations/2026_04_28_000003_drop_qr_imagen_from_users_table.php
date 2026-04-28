<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La columna qr_imagen en users fue añadida por error; el QR vive en tenant_user
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('qr_imagen');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('qr_imagen')->nullable()->after('imagen');
        });
    }
};
