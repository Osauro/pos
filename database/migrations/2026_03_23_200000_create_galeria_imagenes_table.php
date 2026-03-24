<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galeria_imagenes', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('nombre')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedInteger('veces_usado')->default(0);
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galeria_imagenes');
    }
};
