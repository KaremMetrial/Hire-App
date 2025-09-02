<?php

    use App\Http\Controllers\Api\OtpController;
    use App\Http\Controllers\Api\Vendor\AuthController;
    use App\Http\Controllers\Api\Vendor\WorkingDayController;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Api\CountryController;
    use App\Http\Controllers\Api\BrandController;
    // Api Version 1
    Route::prefix('v1')->group(function () {

        // Country
        Route::get('/countries', [CountryController::class, 'index']);
        Route::get('/countries/{country}', [CountryController::class, 'show']);
        Route::get('/countries/{country}/cities', [CountryController::class, 'cities']);

        // Brand
        Route::get('/brands', [BrandController::class, 'index']);
        Route::get('/brands/{brand}', [BrandController::class, 'show']);

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

        // Authintication Middleware
        Route::middleware('auth:vendor')->group(function () {
            // Working Day
            Route::prefix('working-day')->controller(WorkingDayController::class)->group(function () {
                Route::get('{id}/index', 'index'); // get it by rental shop id
                Route::post('/store', 'store');
                Route::put('/{id}/update', 'update');
            });
        });
    });
