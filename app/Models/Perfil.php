<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    protected $table = 'perfil';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $keyType = 'int';
    protected $fillable = ['nombre', 'estado', 'created_at', 'updated_at'];

    public function permisos()
    {
        return $this->hasMany(PermisoMenu::class, 'id_perfil');
    }
}
