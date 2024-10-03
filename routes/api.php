<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\TokenController;
use App\Http\Middleware\VerifyJWTToken;
use App\Http\Middleware\VerifyAdminToken;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PagesController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/send-email-verification-code', [AuthController::class, 'sendEmailVerificationCode']);
Route::post('/verify-email-verification-code', [AuthController::class, 'verifyEmailVerificationCode']);
Route::post('/is-token-active', [TokenController::class, 'isTokenActive']);
// Route::post('/register', [AuthController::class, 'createAccount'])->middleware(VerifyJWTToken::class);

    
Route::middleware([VerifyJWTToken::class])->group(function () {
    Route::post('/register', [AuthController::class, 'createAccount']);
    // Add other routes that need token protection here
});
Route::post('/login', [AuthController::class, 'login']);


// -----------------------------------admin routes----------------------------------------//
Route::post('/admin/login', [AdminAuthController::class, 'adminLogin']);
Route::post('/is-admin-token-active', [AdminAuthController::class, 'isAdminTokenActive'])->middleware(VerifyAdminToken::class);
Route::post('/admin/create-product', [ProductController::class, 'createProduct'])->middleware(VerifyAdminToken::class);
Route::get('/admin/get-page', [PagesController::class, 'index'])->middleware(VerifyAdminToken::class);
Route::post('/admin-settings', [AdminAuthController::class, 'settings'])->middleware(VerifyAdminToken::class);


