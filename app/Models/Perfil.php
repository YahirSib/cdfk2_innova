<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    protected $table = 'perfil';
    protected $fillable = ['nombre', 'estado'];

    public function permisos()
    {
        return $this->hasMany(PermisoMenu::class, 'id_perfil');
    }
}
