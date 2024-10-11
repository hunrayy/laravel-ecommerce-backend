<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\TokenController;
use App\Http\Middleware\VerifyJWTToken;
use App\Http\Middleware\VerifyAdminToken;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\GetPagesController;
use App\Http\Controllers\EditPagesController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserPasswordResetController;
use App\Http\Controllers\AdminPasswordResetController;





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
Route::get('/get-all-products', [ProductController::class, 'getAllProducts']);
Route::get('/get-single-product', [ProductController::class, 'getSingleProduct']);
Route::get('/search-products', [ProductController::class, 'searchProducts']);
Route::get('/get-number-of-days-of-delivery', [AuthController::class, 'getNumberOfDaysOfDelivery'])->middleware(VerifyJWTToken::class);
Route::post('/flutterwave/make-payment', [PaymentController::class, 'makePayment'])->middleware(VerifyJWTToken::class);
Route::get('flutterwave/validate-payment', [PaymentController::class, 'validatePayment'])->middleware(VerifyJWTToken::class);
Route::post('/save-products-to-db-after-payment', [OrderController::class, 'saveProductToDbAfterPayment'])->middleware(VerifyJWTToken::class);
Route::post('/get-user-details', [OrderController::class, 'getUserDetails'])->middleware(VerifyJWTToken::class);
Route::post('/send-password-reset-link', [UserPasswordResetController::class, 'sendPasswordResetLink']);
Route::post('/reset-password', [UserPasswordResetController::class, 'resetPassword'])->middleware(VerifyJWTToken::class);





// -----------------------------------admin routes----------------------------------------//
Route::post('/admin/login', [AdminAuthController::class, 'adminLogin']);
Route::post('/admin-send-password-reset-link', [AdminPasswordResetController::class, 'sendAdminPasswordResetLink']);
Route::post('/admin-reset-password', [AdminPasswordResetController::class, 'AdminResetPassword'])->middleware(VerifyJWTToken::class);
Route::post('/is-admin-token-active', [AdminAuthController::class, 'isAdminTokenActive'])->middleware(VerifyAdminToken::class);
Route::post('/admin/create-product', [ProductController::class, 'createProduct'])->middleware(VerifyAdminToken::class);
Route::get('/admin/get-page', [GetPagesController::class, 'index'])->middleware(VerifyAdminToken::class);
Route::post('/admin/edit-page', [EditPagesController::class, 'index'])->middleware(VerifyAdminToken::class);
Route::post('/admin-settings', [AdminAuthController::class, 'settings'])->middleware(VerifyAdminToken::class);
Route::post('/admin/update-product', [ProductController::class, 'updateProduct'])->middleware(VerifyAdminToken::class);
Route::post('/admin/delete-product', [ProductController::class, 'deleteProduct'])->middleware(VerifyAdminToken::class);

Route::get('/admin/get-orders', [OrderController::class, 'getOrders'])->middleware(VerifyAdminToken::class);
Route::post('/admin/change-order-status-to-out-for-delivery', [OrderController::class, 'ChangeOrderStatusToOutForDelivery'])->middleware(VerifyAdminToken::class);





