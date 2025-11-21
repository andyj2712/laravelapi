<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('valor_comision', 8, 4)->default(0)->after('precio_venta');
        });
        Schema::table('ventas', function (Blueprint $table) {
            $table->decimal('comision_total', 10, 2)->default(0.00)->after('monto_total');
        });
        Schema::table('detalles_venta', function (Blueprint $table) {
            $table->decimal('comision_item', 10, 4)->default(0.0000)->after('precio_unitario');
        });
    }
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('valor_comision');
        });
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn('comision_total');
        });
        Schema::table('detalles_venta', function (Blueprint $table) {
            $table->dropColumn('comision_item');
        });
    }
};