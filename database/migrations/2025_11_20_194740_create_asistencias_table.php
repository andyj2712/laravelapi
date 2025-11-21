<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
        $table->id();
        // Relación con tu tabla empleados usando tu PK personalizada
        $table->unsignedBigInteger('empleado_id');
        $table->foreign('empleado_id')->references('id_empleado')->on('empleados')->onDelete('cascade');

        $table->date('fecha'); // Fecha del registro
        
        // Los 3 estados que pediste
        $table->enum('estado', ['asistio', 'medio_dia', 'falta']); 
        
        $table->text('observacion')->nullable();
        $table->timestamps();
        
        // Evitar duplicados: Un empleado solo puede tener un registro por día
        $table->unique(['empleado_id', 'fecha']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
