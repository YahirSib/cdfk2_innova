<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\TrabajadoresController;
use App\Http\Controllers\PiezasController;
use App\Http\Controllers\SalasController;
use App\Http\Controllers\NotaPiezaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RenderAppController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AgrupacionSalaController;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');


Route::get('/logout', function () {
    Auth::logout();
    return redirect()->route('login');
})->name('logout')->middleware('auth');


Route::get('/',[RenderAppController::class, 'index'])->middleware('auth')->name('index');

Route::middleware(['auth'])->prefix('perfil')->group(function () {
    Route::get('/', [PerfilController::class, 'index'])->name('perfil.index');
    Route::get('/edit/{id}', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::post('/', [PerfilController::class, 'store'])->name('perfil.store');
    Route::get('/datatable', [PerfilController::class, 'datatable'])->name('perfil.datatable');
    Route::delete('/{id}', [PerfilController::class, 'destroy'])->name('perfil.delete');
    Route::put('/', [PerfilController::class, 'update'])->name('perfil.update');
});

Route::middleware(['auth'])->prefix('usuarios')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('usuarios.index');
    Route::get('/edit/{id}', [UserController::class, 'edit'])->name('usuarios.edit');
    Route::post('/', [UserController::class, 'store'])->name('usuarios.store');
    Route::get('/datatable', [UserController::class, 'datatable'])->name('usuarios.datatable');
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('usuarios.delete');
    Route::put('/', [UserController::class, 'update'])->name('usuarios.update');
    Route::post('/reset', [UserController::class, 'reset_pass'])->name('usuarios.reset');
});

Route::middleware(['auth'])->prefix('trabajadores')->group(function () {
    Route::get('/', [TrabajadoresController::class, 'index'])->name('trabajadores.index');
    Route::get('/edit/{id}', [TrabajadoresController::class, 'edit'])->name('trabajadores.edit');
    Route::post('/', [TrabajadoresController::class, 'store'])->name('trabajadores.store');
    Route::get('/datatable', [TrabajadoresController::class, 'datatable'])->name('trabajadores.datatable');
    Route::delete('/{id}', [TrabajadoresController::class, 'destroy'])->name('trabajadores.delete');
    Route::put('/', [TrabajadoresController::class, 'update'])->name('trabajadores.update');
});

Route::middleware(['auth'])->prefix('piezas')->group(function () {
    Route::get('/', [PiezasController::class, 'index'])->name('piezas.index');
    Route::get('/edit/{id}', [PiezasController::class, 'edit'])->name('piezas.edit');
    Route::post('/', [PiezasController::class, 'store'])->name('piezas.store');
    Route::get('/datatable', [PiezasController::class, 'datatable'])->name('piezas.datatable');
    Route::delete('/{id}', [PiezasController::class, 'destroy'])->name('piezas.delete');
    Route::put('/', [PiezasController::class, 'update'])->name('piezas.update');
});

Route::middleware(['auth'])->prefix('salas')->group(function () {
    Route::get('/', [SalasController::class, 'index'])->name('salas.index');
    Route::get('/edit/{id}', [SalasController::class, 'edit'])->name('salas.edit');
    Route::post('/', [SalasController::class, 'store'])->name('salas.store');
    Route::get('/datatable', [SalasController::class, 'datatable'])->name('salas.datatable');
    Route::delete('/{id}', [SalasController::class, 'destroy'])->name('salas.delete');
    Route::put('/', [SalasController::class, 'update'])->name('salas.update');
    Route::post('/getPiezas', [SalasController::class, 'getPiezas'])->name('salas.getPiezas');
    Route::post('/savePiezas', [SalasController::class, 'savePiezas'])->name('salas.savePiezas');
    Route::post('/getPiezasBySala', [SalasController::class, 'getPiezasBySala'])->name('salas.getPiezasBySala');
    Route::delete('/deletePiezid_detalleaBySala/{id}', [SalasController::class, 'deletePiezaBySala'])->name('salas.deletePiezaBySala');
    Route::put('/updatePiezaBySala', [SalasController::class, 'updatePiezaBySala'])->name('salas.updatePiezaBySala');
});

Route::middleware(['auth'])->prefix('nota-pieza')->group(function () {
    Route::get('/', [NotaPiezaController::class, 'index'])->name('nota-pieza.index');
    Route::get('/create', [NotaPiezaController::class, 'create'])->name('nota-pieza.create');
    Route::post('/', [NotaPiezaController::class, 'store'])->name('nota-pieza.store');
    Route::get('/datatable', [NotaPiezaController::class, 'datatable'])->name('nota-pieza.datatable');
    Route::get('/edit/{id}', [NotaPiezaController::class, 'edit'])->name('nota-pieza.edit');
    Route::post('/update', [NotaPiezaController::class, 'update'])->name('nota-pieza.update');
    Route::post('/savePiezas', [NotaPiezaController::class, 'guardarDetalle'])->name('nota-pieza.savePiezas');
    Route::get('/getPiezas/{id}', [NotaPiezaController::class, 'cargarDetalles'])->name('nota-pieza.getPiezas');
    Route::delete('/deletePieza/{id}', [NotaPiezaController::class, 'borrarDetalle'])->name('nota-pieza.deletePieza');
    Route::put('/updatePieza/{id}/{cant}', [NotaPiezaController::class, 'actualizarDetalle'])->name('nota-pieza.updatePieza');
    Route::delete('/{id}', [App\Http\Controllers\NotaPiezaController::class, 'destroy'])->name('nota-pieza.delete');
    Route::get('/print/{id}', [NotaPiezaController::class, 'imprimirPreliminar'])->name('nota-pieza.print_preliminar');
    Route::get('/print-final/{id}', [NotaPiezaController::class, 'imprimirFinal'])->name('nota-pieza.print_final');
    Route::get('/print-historico/{id}', [NotaPiezaController::class, 'imprimirHistorico'])->name('nota-pieza.print_historico');
    Route::get('/print-anulada/{id}', [NotaPiezaController::class, 'imprimirAnular'])->name('nota-pieza.print_anulada');
});


Route::middleware(['auth'])->prefix('agrupacion-sala')->group(function () {
    Route::get('/', [AgrupacionSalaController::class, 'index'])->name('agrupacion-sala.index');
    Route::get('/create', [AgrupacionSalaController::class, 'create'])->name('agrupacion-sala.create');
    // Route::post('/', [AgrupacionSalaController::class, 'store'])->name('agrupacion-sala.store');
    // Route::get('/datatable', [AgrupacionSalaController::class, 'datatable'])->name('agrupacion-sala.datatable');
    // Route::get('/edit/{id}', [AgrupacionSalaController::class, 'edit'])->name('agrupacion-sala.edit');
    // Route::post('/update', [AgrupacionSalaController::class, 'update'])->name('agrupacion-sala.update');
    // Route::post('/savePiezas', [AgrupacionSalaController::class, 'guardarDetalle'])->name('agrupacion-sala.savePiezas');
    // Route::get('/getPiezas/{id}', [AgrupacionSalaController::class, 'cargarDetalles'])->name('agrupacion-sala.getPiezas');
    // Route::delete('/deletePieza/{id}', [AgrupacionSalaController::class, 'borrarDetalle'])->name('agrupacion-sala.deletePieza');
    // Route::put('/updatePieza/{id}/{cant}', [AgrupacionSalaController::class, 'actualizarDetalle'])->name('agrupacion-sala.updatePieza');
    // Route::delete('/{id}', [App\Http\Controllers\AgrupacionSalaController::class, 'destroy'])->name('agrupacion-sala.delete');
    // Route::get('/print/{id}', [AgrupacionSalaController::class, 'imprimirPreliminar'])->name('agrupacion-sala.print_preliminar');
    // Route::get('/print-final/{id}', [AgrupacionSalaController::class, 'imprimirFinal'])->name('agrupacion-sala.print_final');
    // Route::get('/print-historico/{id}', [AgrupacionSalaController::class, 'imprimirHistorico'])->name('agrupacion-sala.print_historico');
    // Route::get('/print-anulada/{id}', [AgrupacionSalaController::class, 'imprimirAnular'])->name('agrupacion-sala.print_anulada');
});



