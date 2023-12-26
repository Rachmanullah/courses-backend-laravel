<?php

use App\Http\Controllers\UsersController;
use App\Http\Middleware\AuthMiddleware;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

route::post('/users/login', [UsersController::class, 'login'])->name('users.login');
Route::middleware(AuthMiddleware::class)->group(function () {
    Route::controller(UsersController::class)->group(function () {
        route::get('/users', 'index')->name('users.getAll');
        route::get('/users/{id}', 'getdataByid')->name('users.getByid');
        route::post('/users', 'store')->name('users.store');
        route::put('/users/{id}', 'update')->name('users.update');
        route::delete('/users/{id}', 'destroy')->name('users.delete');
        route::delete('/users/logout/{id}', 'logout')->name('users.logout');
    });
});
