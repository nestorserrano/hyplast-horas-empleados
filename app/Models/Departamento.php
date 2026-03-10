<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends SoftlandModel
{
    protected $table = 'C01.DEPARTAMENTO';
    protected $primaryKey = 'DEPARTAMENTO';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'DEPARTAMENTO',
        'DESCRIPCION',
        'JEFE',
        'ACTIVO',
    ];
}
