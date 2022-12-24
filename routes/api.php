<?php

use App\Http\Controllers\BarberController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\ReserveController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TimeController;
use App\Http\Controllers\UserController;
use App\Models\User;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->post('/reserve', [ReserveController::class, 'reserve']);
Route::get('/users', [UserController::class, 'users']);
Route::get('/admintoken', function() {
    return User::first()->createToken(User::first()->phone);
});
Route::middleware('auth:sanctum')->put('/user', [UserController::class, 'edit']);
Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'show']);
Route::middleware('auth:sanctum')->post('/barber', [BarberController::class, 'store']);
Route::middleware('auth:sanctum')->post('/time', [TimeController::class, 'store']);
Route::middleware('auth:sanctum')->resource('/service', ServiceController::class);
Route::middleware('auth:sanctum')->resource('/option', OptionController::class);
Route::resource('/photo', PhotoController::class);