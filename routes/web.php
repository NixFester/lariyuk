<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\IPaymuController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\MidtransPlaygroundController;

// midtrans playground routes

Route::middleware('midtrans.csp')->group(function () {
    Route::get('/midtrans-playground',          [MidtransPlaygroundController::class, 'index'])->name('midtrans.playground');
    Route::post('/midtrans-playground/token',   [MidtransPlaygroundController::class, 'token'])->name('midtrans.playground.token');
    Route::get('/midtrans-playground/diagnose', [MidtransPlaygroundController::class, 'diagnose'])->name('midtrans.playground.diagnose');
});

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
    
    // Re-registration routes (for system error recovery)
    Route::get('/reregister/{token}', [RegistrationController::class, 'showReregister'])->name('reregister');
    Route::post('/reregister/{token}', [RegistrationController::class, 'storeReregister'])->name('reregister.store');
    Route::get('/reregister-success/{invoice}', [RegistrationController::class, 'reregisterSuccess'])->name('reregister.success');
    
    // API endpoints for category availability
    Route::get('/api/categories/{categoryId}/availability', [RegistrationController::class, 'checkCategoryAvailability'])->name('api.category-availability');
    Route::get('/api/events/{eventSlug}/categories-availability', [RegistrationController::class, 'getEventCategoriesAvailability'])->name('api.event-categories-availability');
    
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

// ==========================================
// MIDTRANS PAYMENT ROUTES
// ==========================================
Route::prefix('checkout/midtrans')->name('checkout.midtrans.')->group(function () {
    Route::get('/initiate/{invoice}', [MidtransController::class, 'initiate'])->name('initiate');
    Route::get('/finish/{invoice}', [MidtransController::class, 'finish'])->name('finish');
    Route::get('/unfinish/{invoice}', [MidtransController::class, 'unfinish'])->name('unfinish');
    Route::get('/error/{invoice}', [MidtransController::class, 'error'])->name('error');
    Route::post('/webhook', [MidtransController::class, 'webhook'])->name('webhook')->withoutMiddleware(['web']);
    Route::get('/check-status', [MidtransController::class, 'checkStatus'])->name('check-status');
    Route::get('/manual-check-status', [MidtransController::class, 'manualCheckStatus'])->name('manual-check-status');
    Route::get('/debug', [MidtransController::class, 'debugPage'])->name('debug');
    Route::get('/success/{invoice}', [RegistrationController::class, 'successmidtrans'])->name('success');

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
        Route::get('/registrations/event/{eventId}', [AdminRegistrationController::class, 'byEvent'])->name('registrations.by-event');
        Route::get('/registrations/event/{eventId}/category/{categoryId}', [AdminRegistrationController::class, 'byCategory'])->name('registrations.by-category');
        Route::get('/registrations/event/{eventId}/category/{categoryId}/export', [AdminRegistrationController::class, 'exportByCategory'])->name('registrations.export-by-category');
        Route::get('/registrations/verification', [AdminRegistrationController::class, 'verification'])->name('registrations.verification');
        Route::post('/registrations/{id}/verify-payment', [AdminRegistrationController::class, 'verifyPayment'])->name('registrations.verify-payment');
        Route::post('/registrations/{id}/skip-payment', [AdminRegistrationController::class, 'skipPayment'])->name('registrations.skip-payment');
        Route::get('/registrations/export', [AdminRegistrationController::class, 'export'])->name('registrations.export');
        Route::delete('/registrations/{id}', [AdminRegistrationController::class, 'destroy'])->name('registrations.destroy');
        Route::get('/registrations/{id}', [AdminRegistrationController::class, 'show'])->name('registrations.show');

        // Payment Methods
        Route::resource('payment-methods', PaymentMethodController::class)->except(['show']);
        Route::post('/payment-methods/whatsapp-number', [PaymentMethodController::class, 'updateWhatsAppNumber'])->name('payment-methods.whatsapp-number.update');

        // Apology Emails (System Error Recovery)
        Route::prefix('apology-emails')->name('apology-emails.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ApologyEmailController::class, 'index'])->name('index');
            Route::post('/send-all', [\App\Http\Controllers\Admin\ApologyEmailController::class, 'sendAll'])->name('send-all');
            Route::post('/regenerate-expired', [\App\Http\Controllers\Admin\ApologyEmailController::class, 'regenerateExpired'])->name('regenerate-expired');
            Route::get('/{token}', [\App\Http\Controllers\Admin\ApologyEmailController::class, 'show'])->name('show');
            Route::post('/{token}/send', [\App\Http\Controllers\Admin\ApologyEmailController::class, 'sendOne'])->name('send-one');
        });
    });
});
