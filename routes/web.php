<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\IPaymuController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\Admin\PaymentMethodController;

// ==========================================
// PUBLIC ROUTES
// ==========================================
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('events')->name('events.')->group(function () {
    Route::get('/', [EventController::class, 'index'])->name('index');
    Route::get('/{slug}', [EventController::class, 'show'])->name('show');
});
Route::prefix('checkout')->name('checkout.')->group(function () {
    // Static/specific routes MUST be declared before wildcard routes
    Route::get('/pending/{invoice}', [RegistrationController::class, 'pending'])->name('pending');
    Route::post('/cancel/{invoice}', [RegistrationController::class, 'cancelRegistration'])->name('cancel');
    Route::get('/success/{invoice}', [RegistrationController::class, 'success'])->name('success');
    Route::post('/confirm-payment/{invoice}', [RegistrationController::class, 'confirmPayment'])->name('confirm-payment');
    Route::post('/resend-ticket/{invoice}', [RegistrationController::class, 'resendTicket'])->name('resend-ticket');
    Route::get('/verify/{invoice}', [RegistrationController::class, 'verifyRegistrationLink'])->name('verify');
    Route::post('/register', [RegistrationController::class, 'store'])->name('store');
    Route::get('/{event}/{category}', [RegistrationController::class, 'show'])->name('show');
});

// ==========================================
// IPAYMU PAYMENT ROUTES
// ==========================================
Route::prefix('checkout/ipaymu')->name('checkout.ipaymu.')->group(function () {
    Route::get('/initiate/{invoice}', [IPaymuController::class, 'initiate'])->name('initiate');
    Route::get('/check-status', [IPaymuController::class, 'checkStatus'])->name('check-status');
    Route::post('/webhook', [IPaymuController::class, 'webhook'])->name('webhook')->withoutMiddleware(['web']);
    Route::get('/test', [IPaymuController::class, 'testPage'])->name('test');
    Route::post('/test', [IPaymuController::class, 'runTest'])->name('test.run');
});

// API for notifications
Route::get('/api/notifications', [RegistrationController::class, 'getNotifications'])->name('api.notifications');

// IPAYMU TEST PAGE
Route::get('/test', [IPaymuController::class, 'testPage'])->name('ipaymu.test');
Route::post('/test', [IPaymuController::class, 'runTest'])->name('ipaymu.test.run');

// ==========================================
// ADMIN ROUTES
// ==========================================
Route::prefix('admin')->name('admin.')->group(function () {

    // Auth
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Protected Admin Routes
    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminEventController::class, 'dashboard'])->name('dashboard');

        // Event CRUD
        Route::resource('events', AdminEventController::class)->except(['show']);

        // Registrations
        Route::get('/registrations', [AdminRegistrationController::class, 'index'])->name('registrations.index');
        Route::get('/registrations/verification', [AdminRegistrationController::class, 'verification'])->name('registrations.verification');
        Route::post('/registrations/{id}/verify-payment', [AdminRegistrationController::class, 'verifyPayment'])->name('registrations.verify-payment');
        Route::get('/registrations/export', [AdminRegistrationController::class, 'export'])->name('registrations.export');
        Route::delete('/registrations/{id}', [AdminRegistrationController::class, 'destroy'])->name('registrations.destroy');
        Route::get('/registrations/{id}', [AdminRegistrationController::class, 'show'])->name('registrations.show');

        // Payment Methods
        Route::resource('payment-methods', PaymentMethodController::class)->except(['show']);
    });
});
