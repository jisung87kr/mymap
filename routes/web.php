<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ToiletController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/toilet/setting', [ToiletController::class, 'setting']);
Route::get('/toilet/{city?}', [ToiletController::class, 'index']);
Route::put('/toilet/{id}', [ToiletController::class, 'update']);