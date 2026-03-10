<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DepartamentoEmpleado extends SoftlandModel
{
    use HasFactory;
    protected $table = 'DEPARTAMENTO';
    public $timestamps = true;
    protected $guarded = [
        'DEPARTAMENTO',
    ];
    protected $primaryKey = 'DEPARTAMENTO';
    protected $fillable = [
        'DEPARTAMENTO',
        'DESCRIPCION',
    ];

    protected $casts = [
        'DEPARTAMENTO'      => 'string',
        'DESCRIPCION'       => 'string',
    ];
    public function getKeyName(){
        return "DEPARTAMENTO";
    }
}
