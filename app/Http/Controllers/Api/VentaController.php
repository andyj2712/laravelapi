<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Productos;
use App\Http\Resources\VentaResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index()
{
    // 1. Obtenemos las ventas paginadas
    $ventasPaginadas = Venta::with(['empleado', 'productos'])
        ->orderBy('fecha_venta', 'desc')
        ->paginate(15);

    // 2. Calculamos las estadísticas (¡ESTA ES LA PARTE QUE FALTABA!)
    // Nota: Hacemos esto en una consulta separada para no afectar la paginación.
    $queryStats = Venta::query();
    
    $stats = [
        'totalVentas' => $queryStats->count(),
        'ingresosTotales' => $queryStats->sum('monto_total'),
        'totalDescuentos' => $queryStats->sum('descuento'),
        'ventasMes' => $queryStats->whereMonth('fecha_venta', now()->month)
                                 ->whereYear('fecha_venta', now()->year)
                                 ->count(),
    ];

    // 3. Devolvemos la colección de recursos Y añadimos los stats
    return VentaResource::collection($ventasPaginadas)
        ->additional(['stats' => $stats]); // <-- Aquí adjuntamos los stats
}

    public function store(Request $request)
    {
        // 1. Validaciones
        $request->validate([
            'nombre_cliente' => 'required|string|max:100',
            'empleado_id' => 'required|exists:empleados,id_empleado',
            'productos' => 'required|array|min:1',
            'productos.*.id_producto' => 'required|exists:productos,id_producto',
            'productos.*.cantidad' => 'required|numeric|min:0.01',
            'productos.*.precio_unitario' => 'required|numeric|min:0',
            'productos.*.descuento' => 'nullable|numeric|min:0', // Descuento Unitario
        ]);

        // 2. Variables para acumular los totales reales calculados en el servidor
        $calculoMontoTotal = 0;      // Suma de lo que pagará el cliente
        $calculoDescuentoTotal = 0;  // Suma de todos los descuentos aplicados
        $totalComisionGenerada = 0;
        $detallesVenta = [];

        // 3. Pre-cargamos productos para no hacer mil consultas
        $productosEnVenta = $request->productos;
        $productoIds = collect($productosEnVenta)->pluck('id_producto')->toArray();
        
        $productosBD = Productos::select('id_producto', 'nombre_producto', 'stock_disponible', 'valor_comision')
                                ->whereIn('id_producto', $productoIds)
                                ->get()
                                ->keyBy('id_producto');

        // 4. Inicia la Transacción (Todo o Nada)
        DB::beginTransaction();
        try {
            foreach ($productosEnVenta as $item) {
                $producto = $productosBD->get($item['id_producto']);
                
                // Si el producto no existe (raro por la validación, pero por seguridad)
                if (!$producto) continue;

                $cantidad = floatval($item['cantidad']);
                $precioUnitario = floatval($item['precio_unitario']);

                // A. Validar Stock
                if ($producto->stock_disponible < $cantidad) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Stock insuficiente para: ' . $producto->nombre_producto . 
                                     '. Disponible: ' . $producto->stock_disponible
                    ], 409);
                }

                // B. Lógica de Descuento (UNITARIO x CANTIDAD)
                // El frontend manda el descuento por unidad (ej: $5)
                $descuentoUnitario = isset($item['descuento']) ? floatval($item['descuento']) : 0;

                // Seguridad: No descontar más de lo que vale el producto
                if ($descuentoUnitario > $precioUnitario) {
                    $descuentoUnitario = $precioUnitario;
                }

                // El descuento real para la BD es el unitario por la cantidad de items
                $descuentoTotalLinea = $descuentoUnitario * $cantidad;

                // C. Calcular Subtotales
                $subtotalBruto = $cantidad * $precioUnitario;
                $subtotalNeto = $subtotalBruto - $descuentoTotalLinea;

                // D. Acumular a los totales generales de la Venta
                $calculoMontoTotal += $subtotalNeto;
                $calculoDescuentoTotal += $descuentoTotalLinea;

                // E. Calcular Comisión
                $comisionLinea = $producto->valor_comision * $cantidad;
                $totalComisionGenerada += $comisionLinea;

                // F. Preparar datos para la tabla intermedia (detalles_venta)
                $detallesVenta[$item['id_producto']] = [
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'descuento' => $descuentoTotalLinea, // Guardamos el monto total descontado en esta línea
                    'comision_item' => $comisionLinea,
                ];

                // G. Descontar del Inventario
                $producto->stock_disponible -= $cantidad;
                $producto->save();
            }

            // 5. Crear la Venta Principal
            $venta = Venta::create([
                'empleado_id' => $request->empleado_id,
                'nombre_cliente' => $request->nombre_cliente,
                'fecha_venta' => now(),
                'monto_total' => $calculoMontoTotal,
                'descuento' => $calculoDescuentoTotal,
                'comision_total' => $totalComisionGenerada,
            ]);

            // 6. Guardar los Detalles
            $venta->productos()->sync($detallesVenta);

            // 7. Confirmar cambios
            DB::commit();

            // 8. Respuesta
            return (new VentaResource($venta))->response()->setStatusCode(201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al procesar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id_venta)
    {
        $venta = Venta::with(['empleado', 'productos'])->findOrFail($id_venta);
        return new VentaResource($venta);
    }

    public function destroy(Venta $venta)
    {
        // Lógica para devolver stock (opcional)
        // ...

        $venta->delete();
        return response()->json(null, 204);
    }

    // Tu lógica de Dashboard
    public function dashboard()
    {
        $totalMes = Venta::whereMonth('fecha_venta', now()->month)
                            ->whereYear('fecha_venta', now()->year)
                            ->sum('monto_total');

        $ventasPorDia = Venta::selectRaw('DATE(fecha_venta) as dia_completo, SUM(monto_total) as total')
                                ->whereMonth('fecha_venta', now()->month)
                                ->whereYear('fecha_venta', now()->year)
                                ->groupBy('dia_completo')
                                ->orderBy('dia_completo')
                                ->pluck('total', 'dia_completo')
                                ->toArray();

        $labels = [];
        $totales = [];

        foreach ($ventasPorDia as $dia => $total) {
            $labels[] = \Carbon\Carbon::parse($dia)->format('d/M');
            $totales[] = $total;
        }

        return response()->json([
            'totalMes' => $totalMes,
            'labelsDias' => $labels,
            'totalesDias' => $totales
        ]);
    }
}