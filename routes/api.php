<?php

use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group([
    'prefix' => 'orders',
], function () {
    Route::post('/', [OrderController::class, 'create']);
    Route::get('/', [OrderController::class, 'search']);
    Route::post('create-in-background', [OrderController::class, 'createInBackground']);
    Route::put('update-flags', [OrderController::class, 'updateFlags']);
});
