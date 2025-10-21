<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    // Este ya devolvía JSON, así que es perfecto
    public function generarReporte(Request $request)
    {
        $data = $request->validate([
            'mes' => 'nullable|integer|between:1,12',
            'anio' => 'required|integer|min:2020|max:' . date('Y')
        ]);

        $mes = $data['mes'] ?? null;
        $anio = $data['anio'];

        $query = Venta::with('empleado')
            ->select(
                'ventas.id_venta', 'ventas.nombre_cliente', 'ventas.monto_total',
                'ventas.fecha_venta', 'empleados.nombre_empleado'
            )
            ->join('empleados', 'ventas.empleado_id', '=', 'empleados.id_empleado');

        if ($mes) {
            $query->whereMonth('ventas.fecha_venta', $mes);
        }
        $query->whereYear('ventas.fecha_venta', $anio);

        $ventas = $query->get();

        // Añadimos el total de productos a cada venta
        $ventas->each(function ($venta) {
             $venta->total_productos = DB::table('detalles_venta')
                            ->where('venta_id', $venta->id_venta)
                            ->sum('cantidad');
        });

        return response()->json(['ventas' => $ventas]);
    }

    // Este genera un CSV, lo cual está bien para una API
    public function exportarExcel(Request $request)
    {
         $data = $request->validate([
            'mes' => 'nullable|integer|between:1,12',
            'anio' => 'required|integer|min:2020|max:' . date('Y')
        ]);

        $mes = $data['mes'] ?? null;
        $anio = $data['anio'];

        $query = Venta::with('empleado')
            ->select(
                'ventas.fecha_venta', 'ventas.nombre_cliente', 
                'empleados.nombre_empleado as empleado', 'ventas.monto_total'
            )
            ->join('empleados', 'ventas.empleado_id', '=', 'empleados.id_empleado');

        if ($mes) {
            $query->whereMonth('ventas.fecha_venta', $mes);
        }
        $query->whereYear('ventas.fecha_venta', $anio);

        $datos = $query->get();

        $datos->each(function ($venta) {
             $venta->total_productos = DB::table('detalles_venta')
                            ->where('venta_id', $venta->id_venta)
                            ->sum('cantidad');
        });

        $fileName = "reporte_ventas_{$anio}" . ($mes ? "_{$mes}" : "") . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function() use ($datos) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Fecha', 'Cliente', 'Empleado', 'Total Productos', 'Monto Total']);

            foreach ($datos as $venta) {
                fputcsv($file, [
                    $venta->fecha_venta,
                    $venta->nombre_cliente,
                    $venta->empleado, // ya viene como nombre
                    $venta->total_productos,
                    $venta->monto_total
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}