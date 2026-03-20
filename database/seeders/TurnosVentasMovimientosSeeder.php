<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TurnosVentasMovimientosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Deshabilitar restricciones de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate tables
        DB::table('venta_items')->truncate();
        DB::table('ventas')->truncate();
        DB::table('movimientos')->truncate();
        DB::table('turnos')->truncate();

        // Habilitar restricciones de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // IDs de admins
        $admins = [3, 4, 1];
        $adminIndex = 0;

        // Obtener productos disponibles
        $productos = DB::table('productos')->where('estado', true)->get();

        if ($productos->isEmpty()) {
            $this->command->error('No hay productos disponibles. Por favor crea productos primero.');
            return;
        }

        // Crear turnos desde el 29 de diciembre de 2025
        $fechaInicio = Carbon::create(2025, 12, 29);
        $fechaActual = Carbon::now();
        $turnoId = 1;

        echo "Generando turnos, ventas y movimientos...\n";

        while ($fechaInicio->lte($fechaActual)) {
            $fechaFin = $fechaInicio->copy()->addDays(6); // 7 días por turno (lunes a domingo)
            $encargadoId = $admins[$adminIndex];

            // Crear turno con el rango completo (no limitar al día actual)
            DB::table('turnos')->insert([
                'id' => $turnoId,
                'encargado_id' => $encargadoId,
                'fecha_inicio' => $fechaInicio->toDateString(),
                'fecha_fin' => $fechaFin->toDateString(),
                'estado' => $fechaFin->isPast() ? 'finalizado' : 'activo',
                'created_at' => $fechaInicio,
                'updated_at' => $fechaInicio,
            ]);

            echo "Turno #{$turnoId}: {$fechaInicio->format('d/m/Y')} - {$fechaFin->format('d/m/Y')} (Admin: {$encargadoId})\n";

            // Generar ventas y movimientos para cada día del turno
            $fechaDia = $fechaInicio->copy();
            $saldoAcumulado = 1000.00; // Monto inicial del turno

            while ($fechaDia->lte($fechaFin) && $fechaDia->lte($fechaActual)) {
                $numeroVenta = 1;

                // Movimiento inicial del día (monto inicial solo para el primer día)
                if ($fechaDia->eq($fechaInicio)) {
                    DB::table('movimientos')->insert([
                        'turno_id' => $turnoId,
                        'detalle' => 'Monto Inicial',
                        'ingreso' => 1000.00,
                        'egreso' => 0,
                        'saldo' => $saldoAcumulado,
                        'created_at' => $fechaDia->copy()->setTime(8, 0, 0),
                        'updated_at' => $fechaDia->copy()->setTime(8, 0, 0),
                    ]);
                }

                // Generar entre 5 y 15 ventas por día
                $cantidadVentas = rand(5, 15);

                for ($i = 0; $i < $cantidadVentas; $i++) {
                    // Hora aleatoria entre 8:00 y 20:00
                    $hora = rand(8, 20);
                    $minuto = rand(0, 59);
                    $fechaHoraVenta = $fechaDia->copy()->setTime($hora, $minuto, 0);

                    // Generar venta
                    $totalVenta = 0;
                    $cantidadItems = rand(1, 10);
                    $itemsVenta = [];

                    // Seleccionar productos aleatorios para la venta
                    $productosSeleccionados = $productos->random(min($cantidadItems, $productos->count()));

                    foreach ($productosSeleccionados as $producto) {
                        $cantidad = rand(1, 3);
                        $subtotal = $producto->precio * $cantidad;
                        $totalVenta += $subtotal;

                        $itemsVenta[] = [
                            'producto_id' => $producto->id,
                            'cantidad' => $cantidad,
                            'precio' => $producto->precio,
                            'subtotal' => $subtotal,
                            'detalle' => null,
                        ];
                    }

                    // Insertar venta (usuario aleatorio)
                    $userIdAleatorio = $admins[array_rand($admins)];
                    $ventaId = DB::table('ventas')->insertGetId([
                        'user_id' => $userIdAleatorio,
                        'turno_id' => $turnoId,
                        'numero_venta' => $numeroVenta,
                        'fecha_hora' => $fechaHoraVenta,
                        'total' => $totalVenta,
                        'created_at' => $fechaHoraVenta,
                        'updated_at' => $fechaHoraVenta,
                    ]);

                    // Insertar items de la venta
                    foreach ($itemsVenta as $item) {
                        $item['venta_id'] = $ventaId;
                        $item['created_at'] = $fechaHoraVenta;
                        $item['updated_at'] = $fechaHoraVenta;
                        DB::table('venta_items')->insert($item);
                    }

                    // Registrar movimiento de ingreso por venta
                    $saldoAcumulado += $totalVenta;
                    DB::table('movimientos')->insert([
                        'turno_id' => $turnoId,
                        'detalle' => "Venta #{$numeroVenta}",
                        'ingreso' => $totalVenta,
                        'egreso' => 0,
                        'saldo' => $saldoAcumulado,
                        'created_at' => $fechaHoraVenta,
                        'updated_at' => $fechaHoraVenta,
                    ]);

                    $numeroVenta++;
                }

                // Generar egresos aleatorios (1-3 por día)
                $cantidadEgresos = rand(1, 3);
                for ($e = 0; $e < $cantidadEgresos; $e++) {
                    $hora = rand(9, 19);
                    $minuto = rand(0, 59);
                    $fechaHoraEgreso = $fechaDia->copy()->setTime($hora, $minuto, 0);
                    $montoEgreso = rand(50, 200);

                    $conceptos = ['Compra de insumos', 'Pago de servicios', 'Gastos varios', 'Mantenimiento', 'Transporte'];
                    $concepto = $conceptos[array_rand($conceptos)];

                    $saldoAcumulado -= $montoEgreso;
                    DB::table('movimientos')->insert([
                        'turno_id' => $turnoId,
                        'detalle' => $concepto,
                        'ingreso' => 0,
                        'egreso' => $montoEgreso,
                        'saldo' => $saldoAcumulado,
                        'created_at' => $fechaHoraEgreso,
                        'updated_at' => $fechaHoraEgreso,
                    ]);
                }

                echo "  - Día {$fechaDia->format('d/m/Y')}: {$cantidadVentas} ventas, {$cantidadEgresos} egresos\n";

                $fechaDia->addDay();
            }

            // Avanzar al siguiente turno
            $fechaInicio = $fechaFin->copy()->addDay();
            $adminIndex = ($adminIndex + 1) % count($admins);
            $turnoId++;
        }

        echo "\n✓ Seeder completado exitosamente!\n";
        echo "Total de turnos creados: " . ($turnoId - 1) . "\n";
        echo "Total de ventas: " . DB::table('ventas')->count() . "\n";
        echo "Total de movimientos: " . DB::table('movimientos')->count() . "\n";
    }
}
