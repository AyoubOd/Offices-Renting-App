<?php

use App\Http\Controllers\OfficeController;
use App\Http\Controllers\TagController;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Tags...
Route::get('/tags', TagController::class);


// Offices...
Route::get('/offices', [OfficeController::class, 'index']);
Route::get('/offices/{office}', [OfficeController::class, 'show']);

Route::middleware(['auth:sanctum', 'verified',])
    ->prefix('offices')
    ->group(function () {
        Route::post('/', [OfficeController::class, 'create'])
            ->middleware(['ability:office.create']);

        Route::put('/{office}', [OfficeController::class, 'update'])
            ->middleware(['ability:office.update']);

        Route::delete('/{office}', [OfficeController::class, 'delete'])
            ->middleware(['ability:office.delete']);
    });
