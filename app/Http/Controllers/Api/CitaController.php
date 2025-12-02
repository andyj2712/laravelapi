<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CitaController extends Controller
{
    public function index(Request $request)
    {
        $mes = $request->query('mes');
        $anio = $request->query('anio');

        $query = DB::table('citas');

        if ($mes && $anio) {
            $query->whereMonth('fecha_hora', $mes)
                  ->whereYear('fecha_hora', $anio);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:100',
            // VALIDACIÓN CLAVE: La fecha debe ser DESPUÉS de ahora ('now')
            'fecha_hora' => 'required|date|after:now', 
            'color' => 'required|string',
        ], [
            // Mensaje personalizado opcional
            'fecha_hora.after' => 'La fecha y hora de la cita no pueden estar en el pasado.'
        ]);

        DB::table('citas')->insert([
            'titulo' => $request->titulo,
            'fecha_hora' => $request->fecha_hora,
            'comentario' => $request->comentario,
            'color' => $request->color,
            'estado' => 'activa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Cita creada']);
    }

    public function update(Request $request, $id)
    {
        $cita = DB::table('citas')->where('id_cita', $id)->first();
        if (!$cita) return response()->json(['message' => 'No encontrada'], 404);

        if (Carbon::parse($cita->fecha_hora)->isPast()) {
            return response()->json(['message' => 'No puedes editar eventos pasados'], 403);
        }

        DB::table('citas')->where('id_cita', $id)->update([
            'titulo' => $request->titulo,
            'fecha_hora' => $request->fecha_hora,
            'comentario' => $request->comentario,
            'color' => $request->color,
            'estado' => $request->estado ?? 'activa',
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Actualizada']);
    }

    public function destroy($id)
    {
        DB::table('citas')->where('id_cita', $id)->delete();
        return response()->json(['message' => 'Eliminada']);
    }
}