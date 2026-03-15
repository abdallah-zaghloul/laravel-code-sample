<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::view('/', 'welcome');
Route::get('app-state', function () {
    $state = app('state');
    return [
        'reqCount' => $state->reqCount++,
        'reqStaticCount' => $state::$reqStaticCount++,
        'MAX_REQUESTS' => env('MAX_REQUESTS'),
        'process_id' => getmypid()
    ];
});
