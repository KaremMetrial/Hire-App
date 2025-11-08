 <?php

use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CustomerTypeController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\ExtraServiceController;
use App\Http\Controllers\Api\FuelController;
use App\Http\Controllers\Api\InsuranceController;
use App\Http\Controllers\Api\ModelController;
use App\Http\Controllers\Api\OtpController;
use App\Http\Controllers\Api\TransmissionController;
use App\Http\Controllers\Api\Vendor\AuthController;
use App\Http\Controllers\Api\Vendor\BookingController;
use App\Http\Controllers\Api\Vendor\NotificationSettingController;
use App\Http\Controllers\Api\Vendor\RentalShopController;
use App\Http\Controllers\Api\Vendor\VendorController;
use App\Http\Controllers\Api\Vendor\WorkingDayController;
use Illuminate\Support\Facades\Route;

//    use App\Http\Controllers\Api\DocumentController;
// Api Version 1
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

    // Extra Service
    Route::prefix('extra-services')->controller(ExtraServiceController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{extra_service}', 'show');
    });

    // Insurance
    Route::prefix('insurances')->controller(InsuranceController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{insurance}', 'show');
    });

    // Document
    Route::prefix('documents')->controller(DocumentController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{document}', 'show');
    });

    // Customer Type
    Route::prefix('customer-types')->controller(CustomerTypeController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{customer_type}', 'show');
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
        Route::post('/register', 'register')->name('register');
        Route::post('/login', 'login')->name('login');
        Route::post('/forgot-password', 'forgotPassword')->name('forgot-password');
        Route::post('/resend-forgot-password-otp', 'resendForgotPasswordOtp')->name('resend-forgot-password-otp');
        Route::post('/verify-reset-otp', 'verifyResetOtp')->name('verify-reset-otp');
        Route::post('/reset-password', 'resetPassword')->name('reset-password');
    });

    // Authintication Middleware
    Route::middleware('auth:vendor')->group(function () {
        // Working Day
        Route::prefix('working-day')->controller(WorkingDayController::class)->group(function () {
            Route::get('{id}/index', 'index'); // get it by rental shop id
            Route::post('/store', 'store');
            Route::put('{id}/update', 'update');
        });

        // Car
        Route::apiResource('/cars', CarController::class);

        // Me
        Route::controller(VendorController::class)->group(function () {
            Route::get('/me', 'me');
            Route::put('/me', 'update');
        });

        // Rental Shop
        Route::controller(RentalShopController::class)->group(function () {
            Route::put('/rental-shop', 'update');
        });

        // Auth
        Route::controller(AuthController::class)->group(function () {
            Route::post('/logout', 'logout');
            Route::get('/me', 'me');
            Route::put('/update-profile', 'updateProfile');
        });

        // Document
        Route::prefix('documents')->controller(DocumentController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/requirement', 'addRequirement');
        });

        // Notification Setting
        Route::prefix('notification-setting')->controller(NotificationSettingController::class)->group(function () {
            Route::get('/show', 'show');
            Route::put('/update', 'update');
        });

        // Booking
        Route::prefix('bookings')->controller(BookingController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
            Route::post('/{id}/confirm', 'confirm');
            Route::post('/{id}/reject', 'reject');
            Route::post('/{id}/request-info', 'requestInfo');
            Route::post('/{id}/start', 'start');
            Route::post('/{id}/complete', 'complete');
            Route::post('/{id}/confirm-pickup-procedure', 'confirmPickupProcedure');
            Route::post('/{id}/confirm-return-procedure', 'confirmReturnProcedure');
            Route::post('/{id}/approve-extension', 'approveExtension');
            Route::post('/{id}/reject-extension', 'rejectExtension');
            Route::get('/{id}/procedures', 'getProcedures');
        });
    });
});
