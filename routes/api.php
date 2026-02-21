<?php

use App\Http\Controllers\BusinessDayController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PurchaseCategoryController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\DashboardStatsController;

Route::get('/categories', [CategoryController::class, 'index']);
Route::inertia('/addcategory', 'AddCategory');
Route::post('/categories', [CategoryController::class, 'add']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

Route::get('/menu', [MenuController::class, 'index']);

Route::post('/items', [ItemController::class, 'store']);
Route::get('/items/{id}', [ItemController::class, 'edit']);
Route::put('/items/{id}', [ItemController::class, 'update']);
Route::delete('/items/{id}', [ItemController::class, 'destroy']);

Route::get('order_id', [OrderController::class, 'order_id']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders', [OrderController::class, 'index']);
Route::patch('/orders/{order}', [OrderController::class, 'update']);
Route::post('/orders/{order}/bill', [OrderController::class, 'bill']);

Route::get('/purchase_categories', [PurchaseCategoryController::class, 'index']);
Route::post('/purchase_categories', [PurchaseCategoryController::class, 'store']);

Route::get('/purchases', [PurchaseController::class, 'index']);
Route::post('/purchases', [PurchaseController::class, 'store']);


Route::get('/sales', [SaleController::class, 'index']);
Route::get('/sales/daily', [SaleController::class, 'daily']);


Route::prefix('business-day')->group(function () {
    Route::get('/current', [BusinessDayController::class, 'current']);
    Route::post('/open', [BusinessDayController::class, 'open_day']);
    Route::post('/close', [BusinessDayController::class, 'close_day']);
});



Route::prefix('stats')->group(function () {
    Route::get('/monthly-sales', [DashboardStatsController::class, 'monthlySales']);
    Route::get('/monthly-purchases', [DashboardStatsController::class, 'monthlyPurchases']);
    Route::get('/monthly-orders', [DashboardStatsController::class, 'monthlyOrders']);

    Route::get('/weekly-sales', [DashboardStatsController::class, 'weeklySales']);

    Route::get('/daily-sales', [DashboardStatsController::class, 'dailySales']);
});

Route::post('/orders/{order}/bill', [OrderController::class, 'storeBill']);

Route::post('/print/kitchen', [OrderController::class, 'printKitchenBill']);
