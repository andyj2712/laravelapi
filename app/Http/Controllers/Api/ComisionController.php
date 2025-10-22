<?php
// Asegúrate de que el namespace sea 'Api'
namespace App\Http\Controllers\Api; 

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\Empleado;
use Carbon\Carbon;
use App\Http\Resources\VentaResource; // ¡Importante!

class ComisionController extends Controller
{
    /**
     * Muestra la lista de comisiones agregadas por empleado.
     */
    public function index(Request $request)
    {
        // 1. Validar las fechas que vienen de Vue
        $data = $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $start = $data['start_date'];
        $end = $data['end_date'];
        
        // 2. Obtener todos los empleados (tu lógica original)
        $empleados = Empleado::orderBy('nombre_empleado')->get();
        
        $comisiones = [];
        $totalGeneral = 0;

        // 3. Obtener Comisiones Agregadas (tu lógica original)
        $comisionesPorEmpleado = Venta::select('empleado_id')
            ->selectRaw('SUM(comision_total) as total_comision')
            ->whereBetween('fecha_venta', [$start, $end])
            ->whereNotNull('empleado_id')
            ->groupBy('empleado_id')
            ->get();
            
        // 4. Mapear y combinar (tu lógica original)
        foreach ($empleados as $empleado) {
            $comision = $comisionesPorEmpleado->firstWhere('empleado_id', $empleado->id_empleado);
            
            $monto = $comision ? (float) $comision->total_comision : 0.00;
            $totalGeneral += $monto;

            $comisiones[] = [
                'id' => $empleado->id_empleado,
                'nombre' => $empleado->nombre_empleado,
                'monto_comision' => $monto,
            ];
        }

        // 5. DEVOLVER JSON (en lugar de 'return view')
        return response()->json([
            'comisiones' => $comisiones,
            'totalGeneral' => $totalGeneral,
        ]);
    }
    
    /**
     * Muestra el detalle de ventas de un empleado.
     * ¡ESTE ES EL MÉTODO QUE FALLA!
     */
    public function showDetalle(Request $request, $id) 
    {
        // 1. Buscamos al empleado manualmente usando el ID
        $empleado = Empleado::findOrFail($id); 
        // --- FIN DEL CAMBIO ---

        // 2. Validamos las fechas (igual que antes)
        $data = $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $start = $data['start_date'];
        $end = $data['end_date'];

        // 3. Buscamos todas las ventas (igual que antes)
        $ventas = Venta::with('productos','empleado') 
            ->where('empleado_id', $empleado->id_empleado) // Usamos el ID del empleado encontrado
            ->whereBetween('fecha_venta', [$start, $end])
            ->orderBy('fecha_venta', 'desc')
            ->get();
            
        // 4. Devolver JSON (igual que antes)
        return VentaResource::collection($ventas);
    }
}