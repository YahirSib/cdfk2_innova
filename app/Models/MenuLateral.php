<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuLateral extends Model
{
    protected $table = 'menu_lateral';
    protected $primaryKey = 'id_menu';
    public $timestamps = false;

    public function hijos()
    {
        return $this->hasMany(MenuLateral::class, 'padre');
    }

    public function padre()
    {
        return $this->belongsTo(MenuLateral::class, 'padre');
    }

    public function permisos()
    {
        return $this->hasMany(PermisoMenu::class, 'id_menu');
    }
}

