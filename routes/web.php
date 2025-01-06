<?php

use App\Http\Controllers\EkspedisiController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SearchHistoryController; // Tambahkan import untuk SearchHistoryController
use App\Http\Controllers\TransaksiAdminController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Home & Shop Routes
Route::get('/', [TransaksiController::class, 'index'])->name('home');
Route::get('/shop', [Controller::class, 'shop'])->name('shop');

// Tambahkan routing untuk riwayat pencarian
Route::middleware(['auth'])->group(function () {
    Route::get('/search-history', [SearchHistoryController::class, 'index'])->name('search.history');
    Route::post('/search-history/store', [SearchHistoryController::class, 'store'])->name('storeSearchHistory');
    Route::post('/search-history/delete', [SearchHistoryController::class, 'delete'])->name('deleteSearchHistory');
});

// Transaksi Routes
Route::get('/transaksi', [Controller::class, 'transaksi'])->name('transaksi');

// Admin Pembayaran Routes
Route::get('/admin/pembayaran', [Controller::class, 'pembayaran'])->name('pembayaran');
Route::get('/admin/pembayaran/addPembayaran', [Controller::class, 'addPembayaran'])->name('addPembayaran');
Route::post('/admin/pembayaran/savePembayaran', [Controller::class, 'savePembayaran'])->name('savePembayaran');
Route::get('/admin/pembayaran/edit/{id}', [Controller::class, 'editPembayaran'])->name('editPembayaran');
Route::post('/admin/pembayaran/update/{id}', [Controller::class, 'updatePembayaran'])->name('updatePembayaran');
Route::get('/admin/pembayaran/deleteData/{id}', [Controller::class, 'destroyPembayaran'])->name('deletePembayaran');

// Transaksi & Checkout Routes
Route::post('/addTocart', [TransaksiController::class, 'addTocart'])->name('addTocart');
Route::post('/checkout/proses/{id}', [Controller::class, 'prosesCheckout'])->name('checkout.product');
Route::get('/checkout', [Controller::class, 'checkout'])->name('checkout');
Route::post('/checkout/prosesPembayaran', [Controller::class, 'prosesPembayaran'])->name('checkout.bayar');

// User Management Routes
Route::post('/storePelanggan', [UserController::class, 'storePelanggan'])->name('storePelanggan');
Route::post('/login_pelanggan', [UserController::class, 'loginProses'])->name('loginproses.pelanggan');
Route::get('/logout_pelanggan', [UserController::class, 'logout'])->name('logout.pelanggan');

// Admin Authentication Routes
Route::get('/admin', [Controller::class, 'login'])->name('login');
Route::post('/admin/loginProses', [Controller::class, 'loginProses'])->name('loginProses');
Route::get('/admin/logout', [Controller::class, 'logout'])->name('logout');

// Admin Dashboard & Product Management Routes
Route::get('/admin/dashboard', [Controller::class, 'admin'])->name('admin');
Route::get('/admin/product', [ProductController::class, 'index'])->name('product');
Route::post('/admin/addData', [ProductController::class, 'store'])->name('addData');
Route::get('/admin/editModal/{id}', [ProductController::class, 'show'])->name('editModal');
Route::put('/admin/updateData/{id}', [ProductController::class, 'update'])->name('updateData');
Route::delete('/admin/deleteData/{id}', [ProductController::class, 'destroy'])->name('deleteData');

// Admin User Management Routes
Route::get('/admin/user_management', [UserController::class, 'index'])->name('userManagement');
Route::get('/admin/user_management/addModalUser', [UserController::class, 'addModalUser'])->name('addModalUser');
Route::post('/admin/user_management/addData', [UserController::class, 'store'])->name('addDataUser');
Route::get('/admin/user_management/editUser/{id}', [UserController::class, 'show'])->name('showDataUser');
Route::put('/admin/user_management/updateDataUser/{id}', [UserController::class, 'update'])->name('updateDataUSer');
Route::delete('/admin/user_management/deleteUser/{id}', [UserController::class, 'destroy'])->name('destroyDataUser');

// Admin Ekspedisi Routes
Route::get('/admin/ekspedisi', [Controller::class, 'ekspedisi'])->name('ekspedisi');
Route::get('/admin/ekspedisi/addEkspedisi', [Controller::class, 'addEkspedisi'])->name('addEkspedisi');
Route::post('/admin/ekspedisi/saveEkspedisi', [Controller::class, 'saveEkspedisi'])->name('saveEkspedisi');
Route::get('/admin/ekspedisi/edit/{id}', [Controller::class, 'editEkspedisi'])->name('editEkspedisi');
Route::post('/admin/ekspedisi/update/{id}', [Controller::class, 'updateEkspedisi'])->name('updateEkspedisi');
Route::get('/admin/ekspedisi/deleteData/{id}', [Controller::class, 'destroyEkspedisi'])->name('deleteEkspedisi');
