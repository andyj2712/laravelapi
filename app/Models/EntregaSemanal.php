<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntregaSemanal extends Model
{
    use HasFactory;

    protected $table = 'entregas_semanales';
    protected $primaryKey = 'id_entrega';

    protected $fillable = [
        'fecha',
        'camion_info',
        'detalle_materiales',
        'subtotal',
        'iva',
        'total_final',
    ];

    // Casts automáticos
    protected $casts = [
        'camion_info' => 'array',
        'detalle_materiales' => 'array',
        'fecha' => 'date',
    ];

    
    protected $appends = ['fecha_simple'];

    // Accessor: DD/MM/YYYY
    public function getFechaSimpleAttribute()
    {
        return $this->fecha
            ? $this->fecha->format('d/m/Y')
            : null;
    }
}
