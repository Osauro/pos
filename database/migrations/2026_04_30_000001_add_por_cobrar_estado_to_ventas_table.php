<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL no permite ALTER COLUMN en ENUM directamente con Blueprint en todos los drivers,
        // usamos SQL raw para modificar el ENUM y añadir 'PorCobrar'
        DB::statement("ALTER TABLE ventas MODIFY COLUMN estado ENUM('Pendiente','Completo','Cancelado','PorCobrar') NOT NULL DEFAULT 'Completo'");
    }

    public function down(): void
    {
        // Primero convertir cualquier PorCobrar a Pendiente para evitar datos inválidos
        DB::table('ventas')->where('estado', 'PorCobrar')->update(['estado' => 'Pendiente']);
        DB::statement("ALTER TABLE ventas MODIFY COLUMN estado ENUM('Pendiente','Completo','Cancelado') NOT NULL DEFAULT 'Completo'");
    }
};
