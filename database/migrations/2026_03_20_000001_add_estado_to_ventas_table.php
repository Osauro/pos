<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->enum('estado', ['Pendiente', 'Completo', 'Cancelado'])->default('Completo')->after('total');
            $table->decimal('efectivo', 10, 2)->default(0)->after('estado');
            $table->decimal('online', 10, 2)->default(0)->after('efectivo');
            $table->decimal('credito', 10, 2)->default(0)->after('online');
        });

        // Ventas existentes ya son completas
        DB::table('ventas')->update(['estado' => 'Completo']);
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['estado', 'efectivo', 'online', 'credito']);
        });
    }
};
