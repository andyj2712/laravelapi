<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use Illuminate\Http\Request;
use App\Http\Resources\EmpleadoResource;
use Illuminate\Support\Facades\Storage;

class EmpleadoController extends Controller
{
   public function index()
    {
        // 1. Obtenemos los empleados
        // Asumimos que "data" es para paginación, si no, usa ->get()
        $empleados = Empleado::orderBy('nombre_empleado', 'asc')->get(); 

        // 2. Calculamos las estadísticas (como en tu Blade)
        $stats = [
            'totalEmpleados' => $empleados->count(),
            'nominaBase' => $empleados->sum('salario_base'),
            'cargosOcupados' => $empleados->unique('cargo_empleado')->count(),
            'nuevosEn3Meses' => $empleados->where('created_at', '>=', now()->subMonths(3))->count(),
        ];

        // 3. Devolvemos la data y los stats
        return response()->json([
            'data' => $empleados, // Vue leerá esto
            'stats' => $stats      // Vue leerá esto
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_empleado' => 'required|string|max:100',
            'edad_empleado' => 'required|integer|min:18',
            'telefono_empleado' => 'required|string|max:20',
            'salario_base' => 'required|numeric|min:0',
            'cargo_empleado' => 'required|string|max:100',
            'foto_empleado' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('foto_empleado')) {
            // Guarda en 'storage/app/public/empleados'
            $path = $request->file('foto_empleado')->store('public/empleados');
            // Guardamos solo la ruta relativa (ej: 'public/empleados/foto.jpg')
            $data['foto_empleado'] = $path;
        }

        $empleado = Empleado::create($data);

        return (new EmpleadoResource($empleado))->response()->setStatusCode(201);
    }

    public function show($id_empleado)
    {
        $empleado = Empleado::findOrFail($id_empleado);
        return new EmpleadoResource($empleado);
    }

    public function update(Request $request, $id_empleado)
    {
        // OJO: Para manejar 'PUT' con fotos, el frontend debe enviar
        // un POST con un campo '_method' = 'PUT'.

        $empleado = Empleado::findOrFail($id_empleado);

        $data = $request->validate([
            'nombre_empleado' => 'required|string|max:100',
            'edad_empleado' => 'required|integer|min:18',
            'telefono_empleado' => 'required|string|max:20',
            'salario_base' => 'required|numeric|min:0',
            'cargo_empleado' => 'required|string|max:100',
            'foto_empleado' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('foto_empleado')) {
            // Borrar foto anterior
            if ($empleado->foto_empleado) {
                Storage::delete($empleado->foto_empleado);
            }
            // Guardar la nueva
            $path = $request->file('foto_empleado')->store('public/empleados');
            $data['foto_empleado'] = $path;
        }

        $empleado->update($data);

        return new EmpleadoResource($empleado);
    }

    public function destroy($id_empleado)
    {
        $empleado = Empleado::findOrFail($id_empleado);

        // Eliminar la foto si existe
        if ($empleado->foto_empleado) {
            Storage::delete($empleado->foto_empleado);
        }

        $empleado->delete();

        return response()->json(null, 204);
    }
}