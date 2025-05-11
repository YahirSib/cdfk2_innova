<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\TrabajadoresController;

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
