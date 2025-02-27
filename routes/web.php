<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'status'])->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
});

Route::middleware(['auth', 'status'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::controller(ProductController::class)->group(function () {
        Route::get('/product', 'index')->name('product');
        Route::post('/product/add', 'add')->name('product.add');
        Route::get('/product/load/{id}', 'load')->name('product.load');
        Route::post('/product/edit', 'edit')->name('product.update');
        Route::delete('/product/image/delete/{id}',  'deleteImage')->name('product.image.delete');
        Route::delete('/product/delete/{id}',  'deleteProduct')->name('product.delete');
        Route::post('/product/bulk-add', 'bulkAdd')->name('product.bulk.add');
        Route::post('/product/bulk-delete', 'bulkDelete')->name('product.bulk.delete');
    });

    Route::controller(UserController::class)->group(function () {
        Route::get('/sub-admins', 'index')->name('sub-admins');
        Route::post('/sub-admin/add', 'add')->name('sub-admin.add');
        Route::post('/sub-admin/toggle-status','toggleStatus')->name('sub-admin.toggle.status');

    });
});


require __DIR__.'/auth.php';
