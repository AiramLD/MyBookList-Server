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


//Register y Verificacion
Route::post('/register', [UserController::class, 'register']);
Route::get('/email/verify/{id}', [UserController::class, 'verify'])->name('verification.verify');


// Login
Route::post('/login', [UserController::class, 'login'])->name('login');


//Logout
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');

//DeleteAccount
Route::delete('/delete-account', [UserController::class, 'deleteAccount'])->middleware('auth:sanctum');


//Store Book
Route::middleware('auth:sanctum')->post('/add-book', [BookController::class, 'addBook']);



//Show Books
Route::get('/books/{id}', [BookController::class, 'show']);



//UserBook Store
Route::middleware('auth:sanctum')->post('/user-books', [UserBookController::class, 'store']);

//UserBook Show
Route::middleware('auth:sanctum')->post('/user-books/show', [UserBookController::class, 'show']);

//UserBook Update
Route::middleware('auth:sanctum')->put('/user-books', [UserBookController::class, 'update']);

//No salen
Route::post('forgot-password', [UserController::class, 'forgotPassword'])->name('password.email');
Route::post('reset-password', [UserController::class, 'resetPassword'])->name('password.update');

//delete UserBook
Route::middleware('auth:sanctum')->delete('/user-books', [UserBookController::class, 'destroy']);


//remember token
Route::get('/compare-remember-tokens', [UserController::class, 'compareTokens']);

//get User
Route::get('/user', [UserController::class, 'getUser'])->middleware('auth:sanctum');
