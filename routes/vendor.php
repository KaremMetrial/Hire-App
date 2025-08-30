<?php

    use App\Http\Controllers\Api\Vendor\AuthController;
    use App\Http\Controllers\Api\OtpController;
    use Illuminate\Support\Facades\Route;


    // Api Version 1
    Route::prefix('v1')->group(function () {
        Route::post('/otp/send', [OtpController::class, 'sendOtp']);
        Route::post('/otp/verify', [OtpController::class, 'verifyOtp']);

        Route::post('/register', [AuthController::class, 'register'])->middleware('guest')->name('register');

    });
