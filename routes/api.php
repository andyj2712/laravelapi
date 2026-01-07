<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\EmpleadoController;
use App\Http\Controllers\Api\VentaController;
use App\Http\Controllers\Api\ComisionController;
use App\Http\Controllers\Api\ReporteController;
use App\Models\Empleado;
use App\Models\Productos;
use App\Http\Controllers\Api\AsistenciaController;
use App\Http\Controllers\Api\CuadreController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\Api\CitaController;
use App\Http\Controllers\Api\EntregaSemanalController;

/*
|--------------------------------------------------------------------------
| RUTAS DE API - RECICLADORA
|--------------------------------------------------------------------------
*/

// --- Autenticación (Público) ---
Route::post('/login', [AuthController::class, 'login']);

// --- Rutas Protegidas (Cualquier usuario logueado: Admin o Contador) ---
Route::middleware('auth:sanctum')->group(function () {

    // --- Auth ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'getUser']);

    // --- Dashboard y Consultas Generales ---
    Route::get('/dashboard', [VentaController::class, 'dashboard']);

    // --- Cuadre Diario (Lectura) ---
    Route::get('cuadre/consultar', [CuadreController::class, 'consultar']);
    Route::post('cuadre/exportar', [CuadreController::class, 'exportar']);

    // --- Comisiones (Lectura) ---
    Route::get('/comisiones', [ComisionController::class, 'index']);
    Route::get('/comisiones/detalle/{id}', [ComisionController::class, 'showDetalle']);

    // --- Citas (Lectura) ---
    Route::get('/citas', [CitaController::class, 'index']);

    // --- Reportes (Lectura/Generación) ---
    Route::post('/reportes/generar', [ReporteController::class, 'generarReporte']);
    Route::post('/reportes/exportar', [ReporteController::class, 'exportarExcel']);

    // --- Ventas (Lectura) ---
    Route::get('/ventas', [VentaController::class, 'index']); // Historial para todos
    Route::get('/ventas/{venta}', [VentaController::class, 'show']); // Ver detalle
    // MOVIDO AQUÍ: El contador necesita poder descargar el PDF
    Route::get('/ventas/{id}/pdf', [PDFController::class, 'generarFactura']);

    // --- Entregas / Salidas (Lectura) ---
    // MOVIDO AQUÍ: El contador necesita ver el historial de salidas
    Route::get('/entregas-semanales', [EntregaSemanalController::class, 'index']);

    // --- Productos (Lectura) ---
    Route::get('/productos', [ProductoController::class, 'index']);
    Route::get('/productos/{id_producto}', [ProductoController::class, 'show']);

    // --- Recursos para formularios (Selects, etc) ---
    Route::get('/ventas/create-resources', function () {
        $empleados = Empleado::get(['id_empleado', 'nombre_empleado']);
        $productos = Productos::where('stock_disponible', '>', 0)
                                ->get(['id_producto', 'nombre_producto', 'precio_venta', 'valor_comision', 'stock_disponible']);
        return response()->json([
            'empleados' => $empleados,
            'productos' => $productos
        ]);
    });


    // =================================================================
    //  ZONA RESTRINGIDA: SOLO ADMINISTRADORES (Escritura / Borrado)
    // =================================================================
    Route::middleware('role:administrador')->group(function () {

        // --- Gestión de Productos (Crear/Editar/Borrar) ---
        Route::post('/productos', [ProductoController::class, 'store']);
        Route::put('/productos/{id_producto}', [ProductoController::class, 'update']);
        Route::delete('/productos/{id_producto}', [ProductoController::class, 'destroy']);
        Route::post('/productos/{id_producto}/actualizar-stock', [ProductoController::class, 'updateStock']);

        // --- Gestión de Empleados ---
        Route::get('/empleados', [EmpleadoController::class, 'index']); // Lista completa (si es sensible, dejar aquí)
        Route::post('/empleados', [EmpleadoController::class, 'store']);
        Route::get('/empleados/{id_empleado}', [EmpleadoController::class, 'show']);
        Route::post('/empleados/{id_empleado}', [EmpleadoController::class, 'update']);
        Route::delete('/empleados/{id_empleado}', [EmpleadoController::class, 'destroy']);

        // --- Gestión de Ventas (Crear/Borrar) ---
        Route::post('/ventas', [VentaController::class, 'store']); // Solo admin crea venta
        Route::delete('/ventas/{venta}', [VentaController::class, 'destroy']); // Solo admin borra

        // --- Gestión de Asistencias ---
        Route::get('/asistencias', [AsistenciaController::class, 'index']);
        Route::post('/asistencias', [AsistenciaController::class, 'store']);

        // --- Gestión de Entregas/Salidas (Crear/Borrar) ---
        Route::post('/entregas-semanales', [EntregaSemanalController::class, 'store']); // Crear salida
        Route::delete('/entregas-semanales/{id}', [EntregaSemanalController::class, 'destroy']); // Borrar salida

        // --- Gestión de Citas (Escritura) ---
        Route::post('/citas', [CitaController::class, 'store']);
        Route::put('/citas/{id}', [CitaController::class, 'update']);
        Route::delete('/citas/{id}', [CitaController::class, 'destroy']);

    }); // Fin middleware role:administrador

}); // Fin middleware auth:sanctum
