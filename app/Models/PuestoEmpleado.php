<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Position extends SoftlandModel
{
    use HasFactory;
    protected $table = 'puesto';
    public $timestamps = true;
    protected $guarded = [
        'puesto',
    ];
    protected $fillable = [
        'puesto',
        'descripcion',
    ];

    protected $casts = [
        'puesto'            => 'string',
        'descripcion'       => 'string',
    ];
    public function getKeyName(){
        return "puesto";
    }
}
