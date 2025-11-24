<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_empleado';

    protected $fillable = [
        'nombre_empleado',
        'edad_empleado',
        'telefono_empleado',
        'salario_base',
        'foto_empleado',
        'cargo_empleado',
    ];

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'empleado_id', 'id_empleado');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'empleado_id', 'id_empleado');
    }
}