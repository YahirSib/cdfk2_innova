<?php

namespace App\Http\Controllers;
use App\Services\MenuService;

abstract class Controller
{
    private $menu;

    public function __construct()
    {
        $this->menu = MenuService::obtenerMenu();
    }

    public function getMenu()
    {
        return $this->menu;
    }

}
