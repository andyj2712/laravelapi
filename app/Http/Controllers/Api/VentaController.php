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
        $request->validate([
            'nombre_cliente' => 'required|string|max:100',
            'empleado_id' => 'required|exists:empleados,id_empleado',
            'monto_total' => 'required|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0',
            'productos' => 'required|array|min:1',
            'productos.*.id_producto' => 'required|exists:productos,id_producto',
            'productos.*.cantidad' => 'required|numeric|min:0.01',
            'productos.*.precio_unitario' => 'required|numeric|min:0.01'
        ]);

        $totalComisionGenerada = 0;
        $productosEnVenta = $request->productos;
        $productoIds = collect($productosEnVenta)->pluck('id_producto')->toArray();

        $productosBD = Productos::select('id_producto', 'valor_comision', 'stock_disponible', 'nombre_producto')
                                ->whereIn('id_producto', $productoIds)
                                ->get()
                                ->keyBy('id_producto');

        $detallesVenta = [];

        // Usamos una transacción por si algo falla (ej. stock)
        DB::beginTransaction();
        try {
            foreach ($productosEnVenta as $item) {
                $producto = $productosBD->get($item['id_producto']);

                if (!$producto) continue; // No debería pasar

                $cantidadVendida = $item['cantidad'];

                if ($producto->stock_disponible < $cantidadVendida) {
                    // Si no hay stock, cancelamos todo
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Stock insuficiente para el producto: ' . $producto->nombre_producto
                    ], 409); // 409 = Conflicto
                }

                $comisionPorItem = $producto->valor_comision * $cantidadVendida;
                $totalComisionGenerada += $comisionPorItem;

                $detallesVenta[$item['id_producto']] = [
                    'cantidad' => $cantidadVendida,
                    'precio_unitario' => $item['precio_unitario'],
                    'comision_item' => $comisionPorItem,
                ];

                // Descontar Stock
                $producto->stock_disponible -= $cantidadVendida;
                $producto->save();
            }

            $venta = Venta::create([
                'empleado_id' => $request->empleado_id,
                'nombre_cliente' => $request->nombre_cliente,
                'fecha_venta' => now(),
                'monto_total' => $request->monto_total,
                'descuento' => $request->descuento ?? 0,
                'comision_total' => $totalComisionGenerada,
            ]);

            $venta->productos()->sync($detallesVenta);

            // Si todo salió bien, confirmamos
            DB::commit();

            // Cargamos relaciones para devolverla completa
            $venta->load('empleado', 'productos'); 

            return (new VentaResource($venta))->response()->setStatusCode(201);

        } catch (\Exception $e) {
            // Si algo falló, revertimos
            DB::rollBack();
            return response()->json([
                'message' => 'Error al registrar la venta: ' . $e->getMessage()
            ], 500); // 500 = Error de servidor
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