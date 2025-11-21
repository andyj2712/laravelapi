<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Productos;
use Illuminate\Http\Request;
use App\Http\Resources\ProductoResource;

class ProductoController extends Controller
{
    public function index()
    {
        // 1. Obtenemos los productos paginados (o no)
        // Si usas paginación, Vue tendrá que leer 'response.data.data'
        $productos = Productos::orderBy('nombre_producto', 'asc')->get();

        // 2. Calculamos las estadísticas
        $stats = [
            'totalProductos' => $productos->count(),
            'totalStock' => $productos->sum('stock_disponible'),
            'nuevosMes' => $productos->where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        // 3. Devolvemos la colección Y los stats
        // Si NO usas paginación:
        return response()->json([
            'data' => $productos, // Vue leerá 'response.data.data'
            'stats' => $stats
        ]);

        /* // Si SÍ usas paginación (ej. paginate(15)):
        $productosPaginados = Productos::orderBy('nombre_producto', 'asc')->paginate(15);
        return ProductoResource::collection($productosPaginados)
            ->additional(['stats' => $stats]);
        */
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_producto' => 'required|string|max:100',
            'descripcion_producto' => 'nullable|string',
            'precio_venta' => 'required|numeric|min:0',
            'precio_compra' => 'required|numeric|min:0',
            'fecha_ingreso' => 'required|date',
            'stock_disponible' => 'required|integer|min:0',
            'categoria_producto' => 'required|string|max:50',
            'valor_comision' => 'required|numeric|min:0'
        ]);

        $producto = Productos::create($data);

        return (new ProductoResource($producto))
                    ->response()
                    ->setStatusCode(201); // 201 = Creado
    }

    public function show($id_producto)
    {
        $producto = Productos::findOrFail($id_producto);
        return new ProductoResource($producto);
    }

    public function update(Request $request, $id_producto)
    {
        $producto = Productos::findOrFail($id_producto);

        $data = $request->validate([
            'nombre_producto' => 'required|string|max:100',
            'descripcion_producto' => 'nullable|string',
            'precio_venta' => 'required|numeric|min:0',
            'precio_compra' => 'required|numeric|min:0',
            'fecha_ingreso' => 'required|date',
            'stock_disponible' => 'required|integer|min:0',
            'categoria_producto' => 'required|string|max:50',
            'valor_comision' => 'required|numeric|min:0'
        ]);

        $producto->update($data);

        return new ProductoResource($producto);
    }

    public function destroy($id_producto)
    {
        $producto = Productos::findOrFail($id_producto);
        $producto->delete();

        return response()->json(null, 204); // 204 = Sin Contenido
    }

    public function updateStock(Request $request, $id_producto)
    {
        $producto = Productos::findOrFail($id_producto);

        $data = $request->validate([
            'cantidad_nueva' => 'required|integer|min:1',
        ]);

        $producto->stock_disponible += $data['cantidad_nueva'];
        $producto->save();

        return new ProductoResource($producto);
    }
}