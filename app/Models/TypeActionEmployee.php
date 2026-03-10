<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeActionEmployee extends SoftlandModel
{
    use HasFactory;
    protected $table = 'TIPO_ACCION';
    public $timestamps = true;
    protected $guarded = [
        'TIPO_ACCION',
    ];
    protected $primaryKey = 'TIPO_ACCION';
    protected $fillable = [
        'TIPO_ACCION',
        'DESCRIPCION',
    ];

    protected $casts = [
        'PUESTO'            => 'string',
        'DESCRIPCION'       => 'string',
    ];

}
