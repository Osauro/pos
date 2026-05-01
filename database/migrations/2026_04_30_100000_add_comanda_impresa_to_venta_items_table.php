<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venta_items', function (Blueprint $table) {
            $table->boolean('comanda_impresa')->default(false)->after('detalle');
        });
    }

    public function down(): void
    {
        Schema::table('venta_items', function (Blueprint $table) {
            $table->dropColumn('comanda_impresa');
        });
    }
};
