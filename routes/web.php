<?php

use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController as ControllersItemController;
use App\Http\Controllers\OrderController;

// Route::get('/', function () {
//     return Inertia::render('Home', ['users' => User::paginate(10)]);
// })->name('home');

Route::inertia('/register/4477', 'auth/Register')->name('register');
Route::post('/register', [AuthController::class, 'register']);

// main page routes:
Route::inertia('/', 'auth/Login')->name('login');
Route::post('/', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::inertia('/home', 'Home')->name('home');

    Route::inertia('/menu', 'Menu')->name('menu');
    Route::inertia('/orders', 'Orders')->name('orders');
    Route::inertia('/place-order', 'PlaceOrder')->name('place.order');
    Route::inertia('/purchases', 'Purchases')->name('purchases');
    Route::inertia('/sales', 'Sales')->name('sales');

    Route::inertia('/additem', 'AddItem')->name('additem');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
