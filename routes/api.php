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
    Route::controller(AuthController::class)->middleware('guest')->group(function () {
        Route::post('/pre-register', 'preRegister')->name('pre-register');
        Route::post('/complete-registration', 'completeRegistration')->name('complete-registration');
        Route::post('/resend-pre-register-otp', 'resendPreRegisterOtp')->name('resend-pre-register-otp');
//        Route::post('/register', 'register')->name('register'); // Keep old endpoint for backward compatibility
        Route::post('/login', 'login')->name('login');
        Route::post('/forgot-password', 'forgotPassword')->name('forgot-password');
        Route::post('/verify-reset-otp', 'verifyResetOtp')->name('verify-reset-otp');
        Route::post('/reset-password', 'resetPassword')->name('reset-password');
    });

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
        // Auth
        Route::controller(AuthController::class)->group(function () {
            Route::post('/logout', 'logout');
            Route::get('/me', 'me');
            Route::put('/update-profile', 'updateProfile');
            Route::delete('/delete-account', 'deleteAccount');
        });

        // Booking
        Route::prefix('bookings')->controller(BookingController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::post('/{id}/cancel', 'cancel');
            Route::post('/{id}/submit-info', 'submitInfo');
            Route::post('/{id}/report-pickup-issue', 'reportPickupIssue');
            Route::post('/{id}/submit-pickup-procedure', 'submitPickupProcedure');
            Route::post('/{id}/submit-return-procedure', 'submitReturnProcedure');
            Route::post('/{id}/request-extension', 'requestExtension');
            Route::get('/{id}/procedures', 'getProcedures');
        });

        // Accident Reports
        Route::prefix('accident-reports')->controller(BookingController::class)->group(function () {
            Route::post('/', 'submitAccidentReport');
            Route::get('/', 'getAccidentReports');
            Route::get('/{id}', 'getAccidentReport');
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
