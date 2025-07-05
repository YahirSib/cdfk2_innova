<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\PermisoMenu;
use App\Models\MenuLateral;

class MenuService
{

    /**
     * Obtiene el menú lateral basado en los permisos del usuario autenticado.
     *
     * @return array
     */

    public static function obtenerMenu()
    {

        if(!Auth::check()) {
            return []; // Retorna un array vacío si el usuario no está autenticado
        }   

        $perfilId = Auth::user()->perfil_id;

        $permisos = PermisoMenu::where('id_perfil', $perfilId)
            ->where('estado', 1)
            ->pluck('id_menu');

        $menus = MenuLateral::whereIn('id_menu', $permisos)
            ->orderBy('ordenamiento')
            ->get();

        $menu = [];

        foreach ($menus as $item) {
            if (is_null($item->padre)) {
                $menu[$item->nombre] = [
                    'icono' => $item->icono,
                    'hijos' => []
                ];
            }
        }

        foreach ($menus as $item) {
            if (!is_null($item->padre)) {
                $padre = $menus->firstWhere('id_menu', $item->padre);
                if ($padre && isset($menu[$padre->nombre])) {
                    $menu[$padre->nombre]['hijos'][] = [
                        'nombre' => $item->nombre,
                        'ruta' => $item->ruta
                    ];
                }
            }
        }

        return $menu;
    }
}
