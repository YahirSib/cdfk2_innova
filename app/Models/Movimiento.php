<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Detalle;
use Illuminate\Support\Facades\DB;

class Movimiento extends Model
{
    protected $table = 'inv_movimiento';
    protected $primaryKey = 'id_movimiento';
    public $timestamps = false;
    protected $keyType = 'int';
    protected $fillable = [
        'tipo_mov',
        'tipo_doc',
        'fecha_ingreso',
        'cacastero',
        'total',
        'comentario',
        'estado',
        'correlativo',
        'fecha_impresion'
    ];

    public function detalles()
    {
        return $this->hasMany(Detalle::class, 'fk_movimiento', 'id_movimiento');
    }

    public function totalizar()
    {
        return $this->detalles()->sum('costo_total');
    }

    public function totalizarUnidades(){
        return $this->detalles()->sum('unidades');
    }

    public function getNombreCacasteroAttribute()
    {
        $t = DB::table('trabajadores')->where('id_trabajador', $this->cacastero)->first();

        return $t ? "{$t->nombre1} {$t->nombre2} {$t->apellido1} {$t->apellido2}" : null;
    }

    public function getCorrelativoFormateadoAttribute()
    {
        return $this->tipo_doc . '-' . str_pad($this->correlativo, 5, '0', STR_PAD_LEFT);
    }

    public function getFechaIngresoFormateadaAttribute()
    {
        return \Carbon\Carbon::parse($this->fecha_ingreso)->format('d/m/Y');
    }

}
