<?php

use App\Http\Controllers\StateController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('states.index'))->name('home');
Route::get('/states', [StateController::class, 'index'])->name('states.index');
Route::get('/states/{state}/municipalities', [StateController::class, 'municipalities'])
    ->name('states.municipalities');

Route::post('/internal/inegi/sync-states', function (Request $request) {
    $expectedToken = (string) config('services.inegi.sync_token');

    abort_if(
        $expectedToken === '' || ! hash_equals($expectedToken, (string) $request->bearerToken()),
        401,
    );

    $exitCode = Artisan::call('inegi:sync-states');

    return response()->json([
        'ok' => $exitCode === 0,
        'exit_code' => $exitCode,
    ], $exitCode === 0 ? 200 : 500);
})->withoutMiddleware(ValidateCsrfToken::class)
    ->middleware('throttle:2,1')
    ->name('internal.inegi.sync-states');
