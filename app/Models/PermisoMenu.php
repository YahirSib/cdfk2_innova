<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermisoMenu extends Model
{
    protected $table = 'permisos_menu';
    protected $primaryKey = 'id_permiso';
    public $timestamps = false;

    public function menu()
    {
        return $this->belongsTo(MenuLateral::class, 'id_menu');
    }

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'id_perfil');
    }
}

