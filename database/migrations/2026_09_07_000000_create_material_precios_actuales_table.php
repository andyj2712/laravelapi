<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/*
 * Coloca este archivo en: database/migrations/
 * (mantén el prefijo de fecha o Laravel se quejará del orden; puedes
 *  renombrarlo con la fecha de hoy si prefieres)
 */
return new class extends Migration
{
    /**
     * Tabla "singleton": siempre va a tener una sola fila (id = 1)
     * con el precio actual de cada material. El historial de qué
     * precio se usó en cada venta ya vive en material_pesado_reports,
     * así que esta tabla NO se toca desde ahí; solo representa el
     * precio "vigente hoy" que se usa para precargar el formulario.
     */
    public function up(): void
    {
        Schema::create('material_precios_actuales', function (Blueprint $table) {
            $table->id();
            $table->decimal('bronce', 8, 2)->default(1.65);
            $table->decimal('rac', 8, 2)->default(1.30);
            $table->decimal('acero', 8, 2)->default(0.40);
            $table->decimal('aluminio', 8, 2)->default(0.60);
            $table->decimal('cobre', 8, 2)->default(3.10);
            $table->timestamps();
        });

        // Sembramos la única fila que vamos a usar, con los mismos
        // valores que estaban hardcodeados en el frontend.
        DB::table('material_precios_actuales')->insert([
            'id' => 1,
            'bronce' => 1.65,
            'rac' => 1.30,
            'acero' => 0.40,
            'aluminio' => 0.60,
            'cobre' => 3.10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('material_precios_actuales');
    }
};
