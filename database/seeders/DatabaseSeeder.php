<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // -- Landlord (super admin del sistema) --------------------------------
        $landlord = User::create([
            'nombre'         => 'Nagato',
            'celular'        => '73010688',
            'pin'            => Hash::make('5421'),
            'is_super_admin' => true,
        ]);

        $this->command->info('');
        $this->command->info('✓ Landlord creado');
        $this->command->info("  Nombre  : {$landlord->nombre}");
        $this->command->info("  Celular : {$landlord->celular}");
        $this->command->info('  PIN     : 5421');
        $this->command->info('  Acceso  : /admin');
        $this->command->info('');
        $this->command->info('  Los negocios se crean desde el panel /admin');
    }
}
