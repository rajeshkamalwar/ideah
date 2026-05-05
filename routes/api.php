<?php

use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductOrderController;
use App\Http\Controllers\Api\ProductPurchaseController;
use App\Http\Controllers\Api\WishlistController;

Route::get('/', [HomeController::class, 'index'])->name('api.index');
Route::get('/get-lang', [LanguageController::class, 'getLang']);
Route::get('/get-basic', [HomeController::class, 'getBasic'])->name('api.getBasic');
Route::get('/get-payment', [HomeController::class, 'getPayment'])->name('api.getpayment');

Route::get('/get-categories', [HomeController::class, 'getCategories'])->name('getcategories');

Route::post('/save-fcm-token', [FcmTokenController::class, 'store']);
Route::get('/get-notifications', [FcmTokenController::class, 'getNotifications']);


Route::post('verfiy-payment', [HomeController::class, 'verfiyPayment'])->name('frontend.service.payment.verfiy');

Route::prefix('listings')->group(function () {
    Route::get('/', [ListingController::class, 'index'])->name('api.listings');
    Route::get('/details/{slug}/{id}', [ListingController::class, 'details'])->name('api.listing.details');
    Route::post('/store-review/{id}', [ListingController::class, 'storeReview'])->name('api.listing.store.review');
    Route::post('/contact-message', [ListingController::class, 'contact'])->name('api.listing.contact_message');
});
Route::post('/product/contact-message', [ListingController::class, 'productContact'])->name('api.listing.product.contact_message');

//products
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('api.products');
    Route::get('/details/{slug}', [ProductController::class, 'show'])->name('api.products.details');
    Route::post('/add-to-cart', [ProductController::class, 'addToCart'])->name('api.products.add_to_cart');
    Route::post('/remove-product', [ProductController::class, 'removeProductCart'])->name('api.products.remove_cart');
    Route::post('/update-cart', [ProductController::class, 'updateCart'])->name('api.products.update_cart');

    Route::prefix('/checkout')->group(function () {
        Route::get('/', [ProductController::class, 'checkout'])->name('api.products.checkout');
        Route::post('/apply-coupon', [ProductController::class, 'applyCoupon'])->name('api.products.apply_coupon');
        Route::post('/purchase',  [ProductPurchaseController::class, 'index'])->name('api.products.purchase');
    });
});
Route::post('/product/store-review/{id}', [ProductController::class, 'storeReview'])->name('api.product_details.store_review');

//user routes
Route::prefix('user')->group(function () {
    Route::get('/signup', [UserController::class, 'signup'])->name('api.user.signup');
    Route::post('/signup/submit', [UserController::class, 'signupSubmit'])->name('api.user.signup_submit');

    Route::get('/login', [UserController::class, 'login'])->name('api.user.login');
    Route::get('/authentication-fail', [UserController::class, 'authentication_fail'])->name('api.user.authentication.fail');
    Route::post('/login/submit', [UserController::class, 'loginSubmit'])->name('api.user.login_submit');
    //forget password
    Route::post('/forget-password', [UserController::class, 'forget_password'])->name('api.user.forget_password');
    Route::post('/reset-password-update', [UserController::class, 'reset_password_submit'])->name('api.user.update_reset_password');
});

/* ************************************
 * Customer dashboard routes are goes here
 * ************************************/
Route::prefix('/users')->middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('api.users.dashboard');

    //wishlists
    Route::prefix('wishlists')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('api.users.wishlists.index');
        Route::post('/store', [WishlistController::class, 'store'])->name('api.users.wishlists.store');
        Route::post('/delete', [WishlistController::class, 'delete'])->name('api.users.wishlists.delete');
    });

    // product orders
    Route::get('/product-orders', [ProductOrderController::class, 'product_order'])->name('api.users.product_orders');
    Route::get('/product-order/details', [ProductOrderController::class, 'product_order_details'])->name('api.users.product_order.details');

    //claim request
    Route::get('/get-claim-request', [UserController::class, 'getClaimRequest'])->name('api.users.get_claim_request');
    Route::post('/store-claim-request', [UserController::class, 'storeClaimRequestInfo'])->name('api.users.claim_request_info_store');

    //update profile info
    Route::get('/edit-profile', [UserController::class, 'edit_profile'])->name('api.users.edit_profile');
    Route::post('/update/profile', [UserController::class, 'update_profile'])->name('api.users.update_profile');

    //update profile
    Route::post('/update/password', [UserController::class, 'updated_password'])->name('api.users.updated_password');
    Route::post('/logout', [UserController::class, 'logoutSubmit'])->name('api.users.logout');
});
