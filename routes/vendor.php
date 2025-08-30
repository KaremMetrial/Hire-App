<?php

    use App\Http\Controllers\Api\OtpController;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Api\Vendor\AuthController;

    // Api Version 1
    Route::prefix('v1')->group(function () {
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
    });
