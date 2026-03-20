<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario administrador Diego
        Usuario::create([
            'nombre' => 'Diego',
            'celular' => 73010688,
            'pin' => Hash::make('5421'),
            'tipo' => 'admin'
        ]);

        $this->command->info('Usuario creado exitosamente!');
        $this->command->info('Nombre: Diego');
        $this->command->info('Celular: 73010688');
        $this->command->info('PIN: 5421');
        $this->command->info('Tipo: admin');
    }
}
