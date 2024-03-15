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


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::apiResource('user',UserController::class);
// Route::apiResource('book',BookController::class);
// Route::apiResource('userbook',UserBookController::class);




// Login
Route::post('/login', [UserController::class, 'login'])->name('login');

//Logout
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');

//Register y Verificacion
Route::post('/register', [UserController::class, 'register']);
Route::get('/email/verify/{id}', [UserController::class, 'verify'])->name('verification.verify');

//Delete
Route::delete('/usuarios/{usuario}', [UserController::class, 'destroy']);

//Forgot PassWord
Route::post('/password/reset', [UserController::class, 'forgotPassword'])->name('password.reset');

//Show Books
Route::get('/books/{id}', [BookController::class, 'show']);

//Store Book
Route::post('/books', [BookController::class, 'store']);

//UserBook Store
Route::post('/user-books', [UserBookController::class, 'store']);

//UserBook Show
Route::get('/user-books/{userId}/{bookId}', [UserBookController::class, 'show']);

//UserBook Update
Route::put('/user-books/{userId}/{bookId}', [UserBookController::class, 'update']);

