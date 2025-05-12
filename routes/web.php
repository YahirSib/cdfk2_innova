<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\TrabajadoresController;
use App\Http\Controllers\PiezasController;

Route::get('/', function () {
    return view('index');
})->name('index');

Route::prefix('perfil')->group(function () {
    Route::get('/', [PerfilController::class, 'index'])->name('perfil.index');
});

Route::prefix('trabajadores')->group(function () {
    Route::get('/', [TrabajadoresController::class, 'index'])->name('trabajadores.index');
    Route::get('/edit/{id}', [TrabajadoresController::class, 'edit'])->name('trabajadores.edit');
    Route::post('/', [TrabajadoresController::class, 'store'])->name('trabajadores.store');
    Route::get('/datatable', [TrabajadoresController::class, 'datatable'])->name('trabajadores.datatable');
    Route::delete('/{id}', [TrabajadoresController::class, 'destroy'])->name('trabajadores.delete');
    Route::put('/', [TrabajadoresController::class, 'update'])->name('trabajadores.update');
});

Route::prefix('piezas')->group(function () {
    Route::get('/', [PiezasController::class, 'index'])->name('piezas.index');
    Route::get('/edit/{id}', [PiezasController::class, 'edit'])->name('piezas.edit');
    Route::post('/', [PiezasController::class, 'store'])->name('piezas.store');
    Route::get('/datatable', [PiezasController::class, 'datatable'])->name('piezas.datatable');
    Route::delete('/{id}', [PiezasController::class, 'destroy'])->name('piezas.delete');
    Route::put('/', [PiezasController::class, 'update'])->name('piezas.update');
});

