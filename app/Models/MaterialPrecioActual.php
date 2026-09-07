<?php
// Coloca este archivo en: app/Models/MaterialPrecioActual.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialPrecioActual extends Model
{
    use HasFactory;

    protected $table = 'material_precios_actuales';

    protected $guarded = [];

    protected $casts = [
        'bronce' => 'decimal:2',
        'rac' => 'decimal:2',
        'acero' => 'decimal:2',
        'aluminio' => 'decimal:2',
        'cobre' => 'decimal:2',
    ];
}
