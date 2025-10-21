<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\Empleado;
use Carbon\Carbon;

class ComisionController extends Controller
{
    public function index(Request $request)
    {
        $empleados = Empleado::orderBy('nombre_empleado')->get(['id_empleado', 'nombre_empleado']);

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        if (empty($start_date) || empty($end_date)) {
            $start = Carbon::now()->startOfWeek(Carbon::MONDAY);
            $end = Carbon::now()->endOfWeek(Carbon::SUNDAY);
        } else {
            $start = Carbon::parse($start_date)->startOfDay();
            $end = Carbon::parse($end_date)->endOfDay();
        }

        $comisiones = [];
        $totalGeneral = 0;

        $comisionesPorEmpleado = Venta::select('empleado_id')
            ->selectRaw('SUM(comision_total) as total_comision')
            ->whereBetween('fecha_venta', [$start, $end])
            ->whereNotNull('empleado_id')
            ->groupBy('empleado_id')
            ->get();

        foreach ($empleados as $empleado) {
            $comision = $comisionesPorEmpleado->firstWhere('empleado_id', $empleado->id_empleado);
            $monto = $comision ? $comision->total_comision : 0.00;
            $totalGeneral += $monto;

            $comisiones[] = [
                'id' => $empleado->id_empleado,
                'nombre' => $empleado->nombre_empleado,
                'monto_comision' => $monto,
            ];
        }

        return response()->json([
            'comisiones' => $comisiones,
            'totalGeneral' => $totalGeneral,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ]);
    }

    public function showDetalle(Request $request, Empleado $empleado)
    {
        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end = Carbon::parse($data['end_date'])->endOfDay();

        // Traemos las ventas con sus productos y detalles de comisión
        $ventas = Venta::with('productos') 
            ->where('empleado_id', $empleado->id_empleado)
            ->whereBetween('fecha_venta', [$start, $end])
            ->orderBy('fecha_venta', 'desc')
            ->get();

        // Devolvemos los datos crudos, Vue se encarga de mostrarlos
        return response()->json([
            'empleado' => $empleado,
            'ventas' => $ventas,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ]);
    }
}