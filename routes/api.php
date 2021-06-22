<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\TodoController;
use App\Http\Resources\UserResource;
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
    // return new UserResource($request->user());
});

Route::prefix('/v1')->group(function () {
    Route::delete('/logout', [AuthController::class, 'logout']);

    Route::post('/register', [AuthController::class, 'register']);

    // Todo
    Route::prefix('/todo')->middleware(['auth:sanctum'])->group(function () {
        Route::post('/', [TodoController::class, 'store']);
        Route::get('/', [TodoController::class, 'index']);
    });
});
