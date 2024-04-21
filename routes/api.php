<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\UserBookController;
use App\Http\Controllers\UserController;

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

Route::post('/register', [UserController::class, 'register']);
Route::get('/email/verify/{id}', [UserController::class, 'verify'])->name('verification.verify');

Route::post('/login', [UserController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);

Route::middleware('auth:sanctum')->delete('/delete-account', [UserController::class, 'deleteAccount']);

Route::middleware('auth:sanctum')->get('/remember', [UserController::class, 'rememberSession']);

Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'getUser']);

Route::prefix('collection')->middleware('auth:sanctum')->group(function () {
    Route::post('', [UserBookController::class, 'store']);
    Route::delete('/{user_id}/{book_id}', [UserBookController::class, 'destroy']);
    Route::get('/{user_id}/{book_id}', [UserBookController::class, 'show']);
    Route::get('/{user_id}', [UserBookController::class, 'getAll']);
});
    

