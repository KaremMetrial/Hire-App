<?php

use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\FuelController;
use App\Http\Controllers\Api\ModelController;
use App\Http\Controllers\Api\OtpController;
use App\Http\Controllers\Api\TransmissionController;
use App\Http\Controllers\Api\User\AuthController;
use App\Http\Controllers\Api\User\BookingController;
use App\Http\Controllers\Api\User\BookmarkController;
use App\Http\Controllers\Api\User\CarController;
use App\Http\Controllers\Api\User\RentalShopController;
use App\Http\Controllers\Api\User\ReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Country
    Route::prefix('countries')->controller(CountryController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{country}', 'show');
        Route::get('/{country}/cities', 'cities');
    });

    // Brand
    Route::prefix('brands')->controller(BrandController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{brand}', 'show');
    });

    // Model
    Route::prefix('models')->controller(ModelController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{model}', 'show');
    });

    // Fuel
    Route::prefix('fuels')->controller(FuelController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{fuel}', 'show');
    });

    // Transmission
    Route::prefix('transmissions')->controller(TransmissionController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{transmission}', 'show');
    });

    // Category
    Route::prefix('categories')->controller(CategoryController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{category}', 'show');
    });

    // OTP
    Route::prefix('otp')->controller(OtpController::class)->group(function () {
        Route::post('/send', 'sendOtp');
        Route::post('/verify', 'verifyOtp');
    });
    // Auth
    Route::controller(AuthController::class)->group(function () {
        Route::post('/register', 'register')->name('register');
        Route::post('/login', 'login')->name('login');
    })->middleware('guest');

    // Booking
    Route::prefix('bookings')->controller(BookingController::class)->group(function () {
        Route::post('/calculate-price', 'calculatePrice');
    });

    // Car (Public Access)
    Route::prefix('cars')->controller(CarController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{car}', 'show');
        Route::get('/rental-shop/{rentalShopId}', 'getByRentalShop');
    });

    // Rental Shop (Public Access)
    Route::prefix('rental-shops')->controller(RentalShopController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{rentalShop}', 'show');
    });

    // Reviews (Public Access)
    Route::prefix('reviews')->controller(ReviewController::class)->group(function () {
        Route::get('/rental-shop/{rentalShopId}', 'getRentalShopReviews');
        Route::get('/{id}', 'show');
    });

    // Authintication Middleware
    Route::middleware('auth:user')->group(function () {
        // Booking
        Route::prefix('bookings')->controller(BookingController::class)->group(function () {
            Route::post('/', 'store');
        });

        // Bookmarks
        Route::prefix('bookmarks')->controller(BookmarkController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/cars', 'cars');
            Route::post('/toggle/{car}', 'toggle');
            Route::get('/check/{car}', 'check');
            Route::delete('/{car}', 'remove');
            Route::get('/count/{car}', 'count');
        });
    });
});
