<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionEmployee extends SoftlandModel
{
    use HasFactory;
    protected $table = 'EMPLEADO_ACC_PER';
    public $timestamps = true;
    protected $guarded = [
        'NUMERO_ACCION',
    ];

    protected $primaryKey = 'NUMERO_ACCION';
    
    protected $fillable = [

        'TIPO_ACCION',
        'FECHA',
        'EMPLEADO',
        'FECHA_RIGE',
        'FECHA_VENCE',
        'NOTAS',
        'DEPARTAMENTO',
        'PUESTO',
    ];

    protected $casts = [

        'TIPO_ACCION'       => 'string',
        'FECHA'             => 'datetime:d-m-Y',
        'FECHA_RIGE'        => 'datetime:d-m-Y',
        'FECHA_VENCE'       => 'datetime:d-m-Y',
        'NOTA'              => 'string',
        'DEPARTAMENTO'      => 'string',
        'PUESTO'            => 'string',
    ];

    public function getKeyName(){
        return "NUMERO_ACCION";
    }

    public function departamento()
    {
        return $this->hasOne(DepartamentoEmpleado::class, 'DEPARTAMENTO', 'DEPARTAMENTO');
    }
    public function empleado()
    {
        return $this->hasOne(Employee::class, 'EMPLEADO', 'EMPLEADO');
    }
    public function puesto()
    {
        return $this->hasOne(PuestoEmpleado::class, 'PUESTO', 'PUESTO');
    }
    public function tipo_acccion()
    {
        return $this->hasOne(TypeActionEmployee::class, 'TIPO_ACCION', 'TIPO_ACCION');
    }

}
