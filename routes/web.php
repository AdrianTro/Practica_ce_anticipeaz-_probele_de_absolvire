<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::view('/despre-noi', 'despre-noi')->name('about');
Route::get('/contacte', [ContactController::class, 'index'])->name('contacte');
Route::post('/contacte/mesaj', [ContactController::class, 'store'])->name('contacte.store');
Route::get('/contacte/conversatie/{contactThread:public_token}', [ContactController::class, 'showThread'])->name('contacte.thread.show');
Route::post('/contacte/conversatie/{contactThread:public_token}', [ContactController::class, 'storeThreadMessage'])->name('contacte.thread.message');
Route::get('/catalog/{category:slug}/{subcategory?}', [ProductController::class, 'category'])->name('categories.show');
Route::get('/produs/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/cautare-produse', [ProductController::class, 'search'])->name('products.search');

Route::get('/cos', [CartController::class, 'index'])->name('cart.index');
Route::post('/cos/promocod-verifica', [CartController::class, 'checkPromocode'])->name('cart.promocode.check');
Route::post('/cos/comanda', [CartController::class, 'checkout'])->name('cart.checkout');
Route::get('/comanda/succes/{order:order_uuid}', [CartController::class, 'success'])->name('orders.success');

Route::prefix('catadmin')->name('admin.')->group(function (): void {
    Route::get('/', [AdminController::class, 'loginForm'])->name('login');
    Route::post('/login', [AdminController::class, 'login'])->name('login.submit');

    Route::middleware('admin.only')->group(function (): void {
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/comenzi/{order:order_uuid}', [AdminController::class, 'showOrder'])->name('orders.show');
        Route::get('/pretentii', [AdminController::class, 'contactThreads'])->name('claims.index');
        Route::get('/pretentii/{contactThread:thread_uuid}', [AdminController::class, 'showContactThread'])->name('claims.show');
        Route::post('/pretentii/{contactThread:thread_uuid}/raspuns', [AdminController::class, 'replyContactThread'])->name('claims.reply');
        Route::patch('/pretentii/{contactThread:thread_uuid}/incheie', [AdminController::class, 'closeContactThread'])->name('claims.close');
        Route::get('/produse/adauga', [AdminController::class, 'createProduct'])->name('products.create');
        Route::post('/produse', [AdminController::class, 'storeProduct'])->name('products.store');
        Route::get('/produse/{product}/edit', [AdminController::class, 'editProduct'])->name('products.edit');
        Route::put('/produse/{product}', [AdminController::class, 'updateProduct'])->name('products.update');
        Route::delete('/produse/{product}', [AdminController::class, 'destroyProduct'])->name('products.destroy');
        Route::post('/categorii', [AdminController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categorii/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
        Route::patch('/categorii/{category}/toggle', [AdminController::class, 'toggleCategory'])->name('categories.toggle');
        Route::delete('/categorii/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');
        Route::post('/subcategorii', [AdminController::class, 'storeSubcategory'])->name('subcategories.store');
        Route::put('/subcategorii/{subcategory}', [AdminController::class, 'updateSubcategory'])->name('subcategories.update');
        Route::patch('/subcategorii/{subcategory}/toggle', [AdminController::class, 'toggleSubcategory'])->name('subcategories.toggle');
        Route::delete('/subcategorii/{subcategory}', [AdminController::class, 'destroySubcategory'])->name('subcategories.destroy');
        Route::post('/promocoduri', [AdminController::class, 'storePromocode'])->name('promocodes.store');
        Route::patch('/promocoduri/{promocode}/toggle', [AdminController::class, 'togglePromocode'])->name('promocodes.toggle');
    });
});

Route::get('/{path}/catadmin', fn () => redirect()->route('admin.login'))->where('path', '.*')->name('admin.redirect.from.anywhere');
