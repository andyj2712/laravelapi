<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    /**
     * Handles the request for Vue.
     * Fetches employee name and *only* the sum (total_productos).
     */
    public function generarReporte(Request $request)
    {
        $data = $request->validate([
            'mes' => 'nullable|integer|between:1,12',
            'anio' => 'required|integer|min:2020|max:' . date('Y')
        ]);

        $mes = $data['mes'] ?? null;
        $anio = $data['anio'];

        // --- Simple query for Vue ---
        $query = Venta::with([
                'empleado' => function ($query) {
                    $query->select('id_empleado', 'nombre_empleado');
                }
            ])
            // Get the SUM of 'cantidad'
            ->withSum('detalleVentas as total_productos', 'cantidad');
        
        if ($mes) {
            $query->whereMonth('ventas.fecha_venta', $mes);
        }
        $query->whereYear('ventas.fecha_venta', $anio);

        $ventas = $query->get();
        
        // Flatten employee name for Vue
        $ventas->each(function ($venta) {
            $venta->nombre_empleado = $venta->empleado ? $venta->empleado->nombre_empleado : 'N/A';
            // We don't add product names here
        });
        
        return response()->json(['ventas' => $ventas]);
    }

    /**
     * Handles the request for Excel.
     * Fetches employee name, the sum (total_productos), AND the product names.
     */
    public function exportarExcel(Request $request)
{
    $data = $request->validate([
        'mes' => 'nullable|integer|between:1,12',
        'anio' => 'required|integer|min:2020|max:' . date('Y')
    ]);

    $mes = $data['mes'] ?? null;
    $anio = $data['anio'];

    // --- Consulta (Igual que antes) ---
    $query = Venta::with([
            'empleado' => function ($query) {
                $query->select('id_empleado', 'nombre_empleado');
            },
            'productos'
        ])
        ->withSum('detalleVentas as total_productos', 'cantidad');
    
    if ($mes) {
        $query->whereMonth('ventas.fecha_venta', $mes);
    }
    $query->whereYear('ventas.fecha_venta', $anio);

    $datos = $query->get();

    $fileName = "reporte_ventas_{$anio}" . ($mes ? "_{$mes}" : "") . ".csv";

    $headers = [
        "Content-type"        => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0"
    ];

    $callback = function() use ($datos) {
        $file = fopen('php://output', 'w');
        
        // 1. BOM para que salgan bien las tildes y ñ (UTF-8)
        fputs($file, "\xEF\xBB\xBF"); 

        // 2. TRUCO OPCIONAL: Si Excel sigue necio, descomenta la línea de abajo.
        // fputs($file, "sep=,\n"); // Esto fuerza a Excel a usar coma, pero a veces se ve feo en la fila 1.

        // 3. Encabezados (Cambiamos el delimiter a COMA ',')
        fputcsv($file, ['Fecha', 'Cliente', 'Empleado', 'Total Productos', 'Productos', 'Monto Total'], ',');

        foreach ($datos as $venta) {
            // Preparamos la lista de productos bonita para que no rompa el CSV
            // Reemplazamos cualquier coma dentro del texto por un guion o espacio para evitar errores
            $listaProductos = $venta->productos->map(function($p) {
                return str_replace(',', ' ', $p->nombre_producto); 
            })->implode(' | '); // Usamos pipe | para separar productos visualmente dentro de la celda

            fputcsv($file, [
                $venta->fecha_venta,
                $venta->nombre_cliente,
                $venta->empleado ? $venta->empleado->nombre_empleado : 'N/A',
                $venta->total_productos, 
                $listaProductos, // Lista limpia
                $venta->monto_total
            ], ','); // <--- AQUÍ ESTÁ LA CLAVE: Usamos coma
        }
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
}