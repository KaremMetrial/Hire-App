<?php

    use App\Http\Controllers\Api\OtpController;
    use App\Http\Controllers\Api\Vendor\AuthController;
    use App\Http\Controllers\Api\Vendor\WorkingDayController;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Api\CountryController;
    use App\Http\Controllers\Api\BrandController;
    use App\Http\Controllers\Api\ModelController;
    use App\Http\Controllers\Api\FuelController;
    use App\Http\Controllers\Api\TransmissionController;
    use App\Http\Controllers\Api\CategoryController;

    // Api Version 1
    Route::prefix('v1')->group(function () {

        // Country
        Route::get('/countries', [CountryController::class, 'index']);
        Route::get('/countries/{country}', [CountryController::class, 'show']);
        Route::get('/countries/{country}/cities', [CountryController::class, 'cities']);

        // Brand
        Route::get('/brands', [BrandController::class, 'index']);
        Route::get('/brands/{brand}', [BrandController::class, 'show']);

        // Model
        Route::get('/models', [ModelController::class, 'index']);
        Route::get('/models/{model}', [ModelController::class, 'show']);

        // Fuel
        Route::get('/fuels', [FuelController::class, 'index']);
        Route::get('/fuels/{fuel}', [FuelController::class, 'show']);

        // Transmission
        Route::get('/transmissions', [TransmissionController::class, 'index']);
        Route::get('/transmissions/{transmission}', [TransmissionController::class, 'show']);

        // Category
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/categories/{category}', [CategoryController::class, 'show']);

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
                Route::put('{id}/update', 'update');
            });
        });
    });
