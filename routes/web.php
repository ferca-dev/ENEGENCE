<?php

use App\Http\Controllers\StateController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('states.index'))->name('home');
Route::get('/states', [StateController::class, 'index'])->name('states.index');
Route::get('/states/{state}/municipalities', [StateController::class, 'municipalities'])
    ->name('states.municipalities');
