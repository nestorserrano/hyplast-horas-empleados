<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Employee extends SoftlandModel
{
    use HasFactory;
    protected $table = 'EMPLEADO';
    public $timestamps = true;
    protected $guarded = [
        'EMPLEADO',
    ];

    protected $primaryKey = 'EMPLEADO';

    protected $fillable = [
        'EMPLEADO','NOMBRE','SEXO','ESTADO_EMPLEADO','ACTIVO',
        'IDENTIFICACION','FECHA_INGRESO','DEPARTAMENTO','PUESTO','TELEFONO1',
    ];

    protected $casts = [
        'EMPLEADO'              => 'string',
        'NOMBRE'                => 'string',
        'SEXO'                  => 'string',
        'ESTADO_EMPLEADO'       => 'string',
        'ACTIVO'                => 'string',
        'IDENTIFICACION'        => 'string',
        'FECHA_INGRESO'         => 'datetime:d-m-Y',
        'DEPARTAMENTO'          => 'string',
        'PUESTO'                => 'string',
        'TELEFONO1'             => 'string',
    ];

    public function getKeyName(){
        return "EMPLEADO";
    }

    public function estado_empleado()
    {
        return $this->hasOne(EstadoEmpleado::class, 'ESTADO_EMPLEADO', 'ESTADO_EMPLEADO');
    }
    public function departamento()
    {
        return $this->hasOne(DepartamentoEmpleado::class, 'DEPARTAMENTO', 'DEPARTAMENTO');
    }
    public function puesto()
    {
        return $this->hasOne(PuestoEmpleado::class, 'PUESTO', 'PUESTO');
    }

    public function getFECHA_INGRESOAttribute($value){
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

}
