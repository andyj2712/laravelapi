<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Asistencia extends Model
{
    use HasFactory;

    protected $fillable = ['empleado_id', 'fecha', 'estado', 'observacion'];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id', 'id_empleado');
    }
}
