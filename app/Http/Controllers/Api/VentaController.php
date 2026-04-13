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
   public function index(Request $request) // <--- 1. Agrega "Request $request" aquí
{
    // 2. Inicia la consulta ("query") pero NO la ejecutes todavía
    $query = Venta::with(['empleado', 'productos']);

    // ---------------------------------------------------------
    // 3. AQUÍ ESTÁ LO QUE FALTABA: LA LÓGICA DE FILTRADO
    // ---------------------------------------------------------

    // Filtro por Fecha (Buscador nuevo)
    if ($request->filled('fecha')) {
        $query->whereDate('fecha_venta', $request->fecha);
    }

    // Filtro por Cliente (Buscador de texto)
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where('nombre_cliente', 'like', "%{$search}%");
    }
    // ---------------------------------------------------------

    // 4. Ahora sí, ordenamos y paginamos la consulta filtrada
    $ventasPaginadas = $query->orderBy('fecha_venta', 'desc')
                             ->paginate(15);

    // 5. Estadísticas (Esto lo tenías bien, lo dejo igual)
    $queryStats = Venta::query();

    $stats = [
        'totalVentas' => $queryStats->count(),
        'ingresosTotales' => $queryStats->sum('monto_total'),
        'totalDescuentos' => $queryStats->sum('descuento'),
        'ventasMes' => $queryStats->whereMonth('fecha_venta', now()->month)
                                  ->whereYear('fecha_venta', now()->year)
                                  ->count(),
    ];

    // 6. Retorno
    return VentaResource::collection($ventasPaginadas)
        ->additional(['stats' => $stats]);
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

    public function destroy($id_venta)
    {
        // Usamos una transacción para asegurar que si algo falla, no se altere el stock a medias
        DB::beginTransaction();
        try {
            // Buscamos la venta con sus productos asociados
            $venta = Venta::with('productos')->findOrFail($id_venta);

            // 1. Recorremos los productos de esta venta para restaurar el stock
            foreach ($venta->productos as $producto) {
                // Accedemos a la cantidad vendida a través de la tabla pivote (detalles_venta)
                $cantidadVendida = $producto->pivot->cantidad;

                // Buscamos el producto en la base de datos y le sumamos el stock
                $productoBd = Productos::find($producto->id_producto);
                if ($productoBd) {
                    $productoBd->stock_disponible += $cantidadVendida;
                    $productoBd->save();
                }
            }

            // 2. Eliminamos la venta (los detalles_venta se borran solos por el cascade de la BD)
            $venta->delete();

            DB::commit();

            return response()->json([
                'message' => 'Venta eliminada y stock restaurado correctamente.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al eliminar la venta: ' . $e->getMessage()
            ], 500);
        }
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
