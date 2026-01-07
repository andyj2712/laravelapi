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

        // --- Complete query for Excel ---
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
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function() use ($datos) {
            $file = fopen('php://output', 'w');

            // BOM para que Excel reconozca tildes y ñ (CORRECTO, DÉJALO ASÍ)
            fputs($file, "\xEF\xBB\xBF");

            // --- CORRECCIÓN AQUÍ ---
            // Eliminamos el tercer parámetro ';'. Por defecto usará ','
            fputcsv($file, ['Fecha', 'Cliente', 'Empleado', 'Total Productos', 'Productos', 'Monto Total']);

            foreach ($datos as $venta) {
                fputcsv($file, [
                    $venta->fecha_venta,
                    $venta->nombre_cliente,
                    $venta->empleado ? $venta->empleado->nombre_empleado : 'N/A',
                    $venta->total_productos,
                    // Nota: fputcsv manejará automáticamente las comas dentro de este string poniéndole comillas
                    $venta->productos->pluck('nombre_producto')->implode(', '),
                    $venta->monto_total
                ]); // <--- AQUÍ TAMBIÉN QUITAMOS EL ';'
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
