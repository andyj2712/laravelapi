<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialPesadoReport extends Model
{
    use HasFactory;

    // protected $fillable = [...] // O puedes usar guarded
    protected $guarded = []; // Permite llenar todos los campos
}