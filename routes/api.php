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
use App\Models\Comisiones;
use App\Http\Controllers\Api\AsistenciaController;
use App\Http\Controllers\Api\CuadreController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\Api\CitaController;

/*
|--------------------------------------------------------------------------
| RUTAS DE API
|--------------------------------------------------------------------------
| Todo aquí ya tiene el prefijo /api/
*/

// --- Autenticación (Público) ---
// POST /api/login
Route::post('/login', [AuthController::class, 'login']);


// --- Rutas Protegidas (Requieren Token) ---
Route::middleware('auth:sanctum')->group(function () {

    // --- Auth ---
    // POST /api/logout
    Route::post('/logout', [AuthController::class, 'logout']);
    // GET /api/user (Para que Vue sepa quién está logueado)
    Route::get('/user', [AuthController::class, 'getUser']);

    // --- Dashboard ---
    // GET /api/dashboard
    Route::get('/dashboard', [VentaController::class, 'dashboard']);


    Route::get('cuadre/consultar', [CuadreController::class, 'consultar']);
    Route::post('cuadre/exportar', [CuadreController::class, 'exportar']);

    // --- Comisiones ---
    // GET /api/comisiones
    Route::get('/comisiones', [ComisionController::class, 'index']);
    // GET /api/comisiones/detalle/{id}
    Route::get('/comisiones/detalle/{id}', [ComisionController::class, 'showDetalle']);


    // --- Citas ---

    Route::get('/citas', [CitaController::class, 'index']);
    Route::post('/citas', [CitaController::class, 'store']);
    Route::put('/citas/{id}', [CitaController::class, 'update']);
    Route::delete('/citas/{id}', [CitaController::class, 'destroy']);
    
    
    // --- Reportes ---
    // POST /api/reportes/generar
    Route::post('/reportes/generar', [ReporteController::class, 'generarReporte']);
    // POST /api/reportes/exportar
    Route::post('/reportes/exportar', [ReporteController::class, 'exportarExcel']);

    // --- CRUD de Ventas (Para todos los logueados) ---
    // GET /api/ventas
    Route::get('/ventas', [VentaController::class, 'index']);
    // GET /api/ventas/create-resources
    Route::get('/ventas/create-resources', function () {
        // Obtenemos solo empleados activos (puedes cambiar esta lógica)
        $empleados = Empleado::where('status', 'activo') // Asumiendo que tienes un 'status'
                             ->get(['id_empleado', 'nombre_empleado']);
        
        // Obtenemos solo productos con stock
        $productos = Productos::where('stock_disponible', '>', 0)
                               ->get(['id_producto', 'nombre_producto', 'precio_venta', 'valor_comision', 'stock_disponible']);
        
        return response()->json([
            'empleados' => $empleados,
            'productos' => $productos
        ]);
    });
    Route::get('/ventas/create-resources', function () {
        
        // Obtenemos solo empleados (puedes filtrar por 'activos' si tienes esa columna)
        $empleados = Empleado::get(['id_empleado', 'nombre_empleado']);
        
        // Obtenemos solo productos con stock
        $productos = Productos::where('stock_disponible', '>', 0)
                               ->get(['id_producto', 'nombre_producto', 'precio_venta', 'valor_comision', 'stock_disponible']);
        
        return response()->json([
            'empleados' => $empleados,
            'productos' => $productos
        ]);
    });
    // POST /api/ventas
    Route::post('/ventas', [VentaController::class, 'store']);
    // GET /api/ventas/{venta}
    Route::get('/ventas/{venta}', [VentaController::class, 'show']);

    Route::get('/ventas/{id}/pdf', [PDFController::class, 'generarFactura']);



    // --- RUTAS SOLO PARA ADMINISTRADORES ---
    Route::middleware('role:administrador')->group(function () {

        // --- CRUD de Productos ---
        // POST /api/productos
        Route::post('/productos', [ProductoController::class, 'store']);
        // PUT /api/productos/{id}
        Route::put('/productos/{id_producto}', [ProductoController::class, 'update']);
        // DELETE /api/productos/{id}
        Route::delete('/productos/{id_producto}', [ProductoController::class, 'destroy']);
        // POST /api/productos/{id}/actualizar-stock
        Route::post('/productos/{id_producto}/actualizar-stock', [ProductoController::class, 'updateStock']);

        // --- CRUD de Empleados ---
        // GET /api/empleados (Lo ponemos aquí para que solo admin los vea)
        Route::get('/empleados', [EmpleadoController::class, 'index']);
        // POST /api/empleados
        Route::post('/empleados', [EmpleadoController::class, 'store']);
        // GET /api/empleados/{id}
        Route::get('/empleados/{id_empleado}', [EmpleadoController::class, 'show']);
        // POST /api/empleados/{id} (Usamos POST para fotos)
        Route::post('/empleados/{id_empleado}', [EmpleadoController::class, 'update']);
        // DELETE /api/empleados/{id}
        Route::delete('/empleados/{id_empleado}', [EmpleadoController::class, 'destroy']);

        // DELETE /api/ventas/{venta} (Solo admin puede borrar ventas)
        Route::delete('/ventas/{venta}', [VentaController::class, 'destroy']);
    
    
        Route::get('/asistencias', [AsistenciaController::class, 'index']);
        Route::post('/asistencias', [AsistenciaController::class, 'store']);
    });

    // --- RUTAS PÚBLICAS (PERO AUTENTICADAS) ---
    // (Cualquier logueado puede ver productos)
    // GET /api/productos
    Route::get('/productos', [ProductoController::class, 'index']);
    // GET /api/productos/{id}
    Route::get('/productos/{id_producto}', [ProductoController::class, 'show']);


    // RUTAS DE REPO
});