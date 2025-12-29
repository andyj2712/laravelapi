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
        Schema::create('entregas_semanales', function (Blueprint $table) {
        $table->id('id_entrega');
        $table->date('fecha');

        // Aquí guardaremos la "foto" del camión (Serie, Placa, Capacidad)
        // Ejemplo: {"serie": "C-87-825", "capacidad": "6,180 kg", "tipo": "interno"}
        $table->json('camion_info');

        // Aquí guardaremos la lista de materiales calculados
        // Ejemplo: [{"material": "Hierro", "precio": 0.10, "libras": 1000, "subtotal": 100}]
        $table->json('detalle_materiales');

        // Totales monetarios
        $table->decimal('subtotal', 10, 2);
        $table->decimal('iva', 10, 2); // El 13% o lo que aplique
        $table->decimal('total_final', 10, 2);

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entregas_semanales');
    }
};
