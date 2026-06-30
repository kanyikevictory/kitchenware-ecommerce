<?php

use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\V1\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Api\V1\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\V1\Admin\ProductImageController as AdminProductImageController;
use App\Http\Controllers\Api\V1\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\AuthenticatedUserController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CartItemController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\CouponController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductReviewController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\User\ChangePasswordController;
use App\Http\Controllers\Api\V1\User\OrderHistoryController;
use App\Http\Controllers\Api\V1\User\OrderStatusController;
use App\Http\Controllers\Api\V1\User\ProfileController;
use App\Http\Controllers\Api\V1\User\ShippingAddressController;
use App\Http\Controllers\Api\V1\WishlistController;
use App\Http\Controllers\Api\V1\WishlistItemController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    Route::get('health', HealthController::class);
    Route::get('search', SearchController::class)->middleware('throttle:60,1');
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category:slug}', [CategoryController::class, 'show']);
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{product:slug}', [ProductController::class, 'show']);
    Route::get('products/{product:slug}/reviews', [ProductReviewController::class, 'index']);

    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:6,1');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:6,1');
        Route::post('forgot-password', [PasswordResetController::class, 'forgot'])->middleware('throttle:3,1');
        Route::post('reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1');

        Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('email/verification-notification', [EmailVerificationController::class, 'send'])
                ->middleware('throttle:6,1')
                ->name('verification.send');
        });
    });

    Route::middleware('auth:sanctum')->get('me', AuthenticatedUserController::class);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [ProfileController::class, 'show']);
        Route::put('profile', [ProfileController::class, 'update']);
        Route::put('profile/password', ChangePasswordController::class);

        Route::middleware('verified')->group(function () {
            Route::get('cart', [CartController::class, 'show']);
            Route::delete('cart', [CartController::class, 'destroy']);
            Route::post('cart/items', [CartItemController::class, 'store']);
            Route::patch('cart/items/{cartItem}', [CartItemController::class, 'update']);
            Route::delete('cart/items/{cartItem}', [CartItemController::class, 'destroy']);

            Route::get('wishlist', [WishlistController::class, 'show']);
            Route::post('wishlist/items', [WishlistItemController::class, 'store']);
            Route::delete('wishlist/items/{wishlistItem}', [WishlistItemController::class, 'destroy']);

            Route::post('checkout', CheckoutController::class)->middleware('throttle:checkout');
            Route::post('coupons/validate', [CouponController::class, 'validateCoupon']);

            Route::apiResource('shipping-addresses', ShippingAddressController::class);
            Route::get('orders', [OrderHistoryController::class, 'index']);
            Route::get('orders/{order}', [OrderHistoryController::class, 'show']);
            Route::post('orders/{order}/cancel', [OrderStatusController::class, 'cancel']);
            Route::get('orders/{order}/payments', [PaymentController::class, 'index']);
            Route::post('orders/{order}/payments', [PaymentController::class, 'store']);

            Route::post('products/{product}/reviews', [ReviewController::class, 'store']);
            Route::put('reviews/{review}', [ReviewController::class, 'update']);
            Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);
        });

        Route::prefix('admin')->middleware(['permission:admin.access', 'throttle:admin'])->group(function () {
            Route::get('dashboard', DashboardController::class);
            Route::get('users', [AdminUserController::class, 'index']);
            Route::patch('users/{user}/status', [AdminUserController::class, 'updateStatus']);
            Route::apiResource('categories', AdminCategoryController::class);
            Route::apiResource('products', AdminProductController::class);
            Route::delete('products/{product}/images/{image}', [AdminProductImageController::class, 'destroy']);
            Route::patch('products/{product}/images/{image}/primary', [AdminProductImageController::class, 'primary']);
            Route::get('orders', [AdminOrderController::class, 'index']);
            Route::get('orders/{order}', [AdminOrderController::class, 'show']);
            Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
            Route::patch('payments/{payment}/cash-on-delivery', [AdminPaymentController::class, 'updateStatus']);
            Route::get('reviews', [AdminReviewController::class, 'index']);
            Route::patch('reviews/{review}/moderation', [AdminReviewController::class, 'moderate']);
            Route::apiResource('coupons', AdminCouponController::class);
        });
    });
});
