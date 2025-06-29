<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Perfil;
use App\Models\PermisoMenu;
use App\Models\MenuLateral;

class MenuController extends Controller
{
    public function obtenerMenu()
    {
        $perfilId = Auth::user()->perfil_id;

        // Obtener los IDs de menú con permiso
        $permisos = PermisoMenu::where('id_perfil', $perfilId)
            ->where('estado', 1)
            ->pluck('id_menu');

        

        // Obtener todos los menús permitidos
        $menus = MenuLateral::whereIn('id_menu', $permisos)->orderBy('ordenamiento')->get();

       

        // Separar menús padres (submenús) y sus hijos
        $menu = [];
        $cont = 0;

        foreach ($menus as $item) {
            
            if (is_null($item->padre)) {
                // Es un submenú (grupo)
                $menu[$item->nombre] = [
                    'icono' => $item->icono,
                    'hijos' => []
                ];
                $cont++;
            }
        }

        // Agregar hijos a su respectivo grupo
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
