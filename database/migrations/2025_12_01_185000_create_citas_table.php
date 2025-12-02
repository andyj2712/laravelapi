<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('citas', function (Blueprint $table) {
        $table->id('id_cita');
        $table->string('titulo');
        $table->dateTime('fecha_hora');
        $table->text('comentario')->nullable();
        $table->string('color')->default('#1976D2'); // Color para diferenciar eventos
        $table->enum('estado', ['activa', 'cancelada'])->default('activa');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
