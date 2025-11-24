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
    Schema::create('material_pesado_reports', function (Blueprint $table) {
        $table->id();
        $table->string('cliente')->default('Ronald');

        // Guardamos las libras
        $table->decimal('bronce_lb', 8, 2)->default(0);
        $table->decimal('rac_lb', 8, 2)->default(0);
        $table->decimal('acero_lb', 8, 2)->default(0);
        $table->decimal('aluminio_lb', 8, 2)->default(0);
        $table->decimal('cobre_lb', 8, 2)->default(0);

        // Guardamos los precios de ESE DÍA (¡importante!)
        $table->decimal('bronce_precio', 8, 2);
        $table->decimal('rac_precio', 8, 2);
        $table->decimal('acero_precio', 8, 2);
        $table->decimal('aluminio_precio', 8, 2);
        $table->decimal('cobre_precio', 8, 2);

        // Guardamos los totales calculados
        $table->decimal('total_bronce', 10, 2);
        $table->decimal('total_rac', 10, 2);
        $table->decimal('total_acero', 10, 2);
        $table->decimal('total_aluminio', 10, 2);
        $table->decimal('total_cobre', 10, 2);
        $table->decimal('total_general', 12, 2);

        $table->timestamps(); // created_at será la fecha del reporte
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_pesado_reports');
    }
};
