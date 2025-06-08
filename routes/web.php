<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\TrabajadoresController;
use App\Http\Controllers\PiezasController;
use App\Http\Controllers\SalasController;

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

Route::prefix('salas')->group(function () {
    Route::get('/', [SalasController::class, 'index'])->name('salas.index');
    Route::get('/edit/{id}', [SalasController::class, 'edit'])->name('salas.edit');
    Route::post('/', [SalasController::class, 'store'])->name('salas.store');
    Route::get('/datatable', [SalasController::class, 'datatable'])->name('salas.datatable');
    Route::delete('/{id}', [SalasController::class, 'destroy'])->name('salas.delete');
    Route::put('/', [SalasController::class, 'update'])->name('salas.update');
    Route::post('/getPiezas', [SalasController::class, 'getPiezas'])->name('salas.getPiezas');
    Route::post('/savePiezas', [SalasController::class, 'savePiezas'])->name('salas.savePiezas');
    Route::post('/getPiezasBySala', [SalasController::class, 'getPiezasBySala'])->name('salas.getPiezasBySala');
    Route::delete('/deletePiezaBySala/{id}', [SalasController::class, 'deletePiezaBySala'])->name('salas.deletePiezaBySala');
    Route::put('/updatePiezaBySala', [SalasController::class, 'updatePiezaBySala'])->name('salas.updatePiezaBySala');
});

Route::prefix('nota-pieza')->group(function () {
    Route::get('/', [App\Http\Controllers\NotaPiezaController::class, 'index'])->name('nota-pieza.index');
    Route::get('/create', [App\Http\Controllers\NotaPiezaController::class, 'create'])->name('nota-pieza.create');
    // Route::post('/store', [App\Http\Controllers\NotaPiezaController::class, 'store'])->name('nota-pieza.store');
    // Route::get('/datatable', [App\Http\Controllers\NotaPiezaController::class, 'datatable'])->name('nota-pieza.datatable');
    // Route::get('/edit/{id}', [App\Http\Controllers\NotaPiezaController::class, 'edit'])->name('nota-pieza.edit');
    // Route::put('/update', [App\Http\Controllers\NotaPiezaController::class, 'update'])->name('nota-pieza.update');
    // Route::delete('/{id}', [App\Http\Controllers\NotaPiezaController::class, 'destroy'])->name('nota-pieza.delete');
});



