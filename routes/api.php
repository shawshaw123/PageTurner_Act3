<?php

use App\Integrations\RestApi\Controllers\AuthController;
use App\Integrations\RestApi\Controllers\BookController;
use App\Integrations\RestApi\Controllers\CategoryController;
use App\Integrations\RestApi\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

/*
| LEBROn REST API, version 1. Every URL starts with /api/v1.
| Documentation: Swagger UI at /admin/api-docs (admins), spec in app/Support/OpenApiSpec.php
|
| Public:    the catalog (books, categories) and getting a token.
| Protected: everything else needs "Authorization: Bearer <token>";
|            without a valid token the answer is HTTP 401.
*/

Route::prefix('v1')->name('api.v1.')->middleware('throttle:api')->group(function () {
    // Authentication
    Route::post('/auth/token', [AuthController::class, 'token'])->middleware('throttle:auth')->name('auth.token');

    // Public catalog
    Route::get('/books', [BookController::class, 'index'])->name('books.index');
    Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

    // Protected (Sanctum token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::post('/books', [BookController::class, 'store'])->name('books.store');
        Route::match(['put', 'patch'], '/books/{book}', [BookController::class, 'update'])->name('books.update');
        Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');

        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::match(['put', 'patch'], '/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    });
});
