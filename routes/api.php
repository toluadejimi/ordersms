<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DashboardApiController;
use App\Http\Controllers\API\WalletApiController;
use App\Http\Controllers\API\Server1VerificationController;
use App\Http\Controllers\API\Server2ApiController;
use App\Http\Controllers\API\Server3ApiController;
use App\Http\Controllers\API\Server4ApiController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\FundingController;
use App\Http\Controllers\API\Server6ApiController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\API\ForgotPasswordController;
use App\Http\Controllers\API\ResetPasswordController;
use App\Http\Middleware\EnforceApiSession;

// 🔓 Public Authentication Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

// 📡 Email verification via email link
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return response()->json(['message' => 'Email verified successfully']);
})->middleware(['signed'])->name('verification.verify');

// 🔐 Protected API Routes
Route::middleware(['auth:sanctum', EnforceApiSession::class])->group(function () {

    Route::get('/user', fn(Request $request) => $request->user());
    Route::get('/dashboard', [DashboardApiController::class, 'data']);
    Route::post('/virtual-account/generate', [WalletApiController::class, 'generateVirtualAccount']);

    // ✅ Email Verification Routes (require auth)
    Route::get('/email/verified-status', function (Request $request) {
        return response()->json([
            'email_verified' => $request->user()?->hasVerifiedEmail() ?? false,
        ]);
    });

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verification link sent']);
    })->name('verification.send');

    // ✅ Server 1 (DaisySMS)
    Route::prefix('server1')->group(function () {
        Route::get('/dashboard', [Server1VerificationController::class, 'dashboard']);
        Route::post('/purchase', [Server1VerificationController::class, 'purchaseNumber']);
        Route::get('/cancel/{id}', [Server1VerificationController::class, 'cancel']);
        Route::get('/poll-code/{id}', [Server1VerificationController::class, 'pollCode']);
    });

    // ✅ Server 2 (Tellabot)
    Route::prefix('server2')->group(function () {
        Route::get('/services', [Server2ApiController::class, 'getServices']);
        Route::get('/data', [Server2ApiController::class, 'getDashboardData']);
        Route::post('/buy', [Server2ApiController::class, 'buy']);
        Route::get('/status/{id}', [Server2ApiController::class, 'Status']);
        Route::get('/read-sms/id/{activationId}', [Server2ApiController::class, 'readSms']);
        Route::get('/cancel/{id}', [Server2ApiController::class, 'cancel']);
    });

    // ✅ Server 3 (SMSPool)
    Route::prefix('server3')->group(function () {
        Route::get('/dashboard', [Server3ApiController::class, 'dashboard']);
        Route::post('/purchase', [Server3ApiController::class, 'purchase']);
        Route::post('/get-price', [Server3ApiController::class, 'getPrice']);
        Route::get('/check/{id}', [Server3ApiController::class, 'check']);
        Route::get('/cancel/{id}', [Server3ApiController::class, 'cancel']);
    });

    // ✅ Server 4 (SMS-Man)
    Route::prefix('server4')->group(function () {
        Route::get('/dashboard', [Server4ApiController::class, 'dashboard']);
        Route::post('/get-price', [Server4ApiController::class, 'getPrice']);
        Route::post('/purchase', [Server4ApiController::class, 'purchase']);
        Route::get('/check/{id}', [Server4ApiController::class, 'check']);
        Route::get('/cancel/{id}', [Server4ApiController::class, 'cancel']);
    });

    // ✅ Add back Server 6 if needed later
});

// 📡 Webhooks
Route::post('/webhook/daisy', [VerificationController::class, 'webhook']);
Route::post('/webhook/paymentpoint', [FundingController::class, 'handlePaymentpointWebhook']);
Route::post('/webhook/sprintpay', [FundingController::class, 'handleSprintpayWebhook']);
