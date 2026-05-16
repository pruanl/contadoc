<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AuthorizedSenderController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EvolutionWebhookController;
use App\Http\Controllers\WhatsappConnectionController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('landing');
})->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'authenticate'])->name('login.store');
    Route::get('/register', [AuthenticatedSessionController::class, 'register'])->name('register');
    Route::post('/register', [AuthenticatedSessionController::class, 'storeRegisteredUser'])->name('register.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::view('/como-enviar', 'send-instructions')->name('send-instructions');
    Route::post('/clients/{client}/activate-whatsapp', [ClientController::class, 'activateWhatsapp'])->name('clients.activate-whatsapp');
    Route::resource('clients', ClientController::class);
    Route::resource('authorized-senders', AuthorizedSenderController::class)->except(['show']);
    Route::get('/documents/{document}/file', [DocumentController::class, 'file'])->name('documents.file');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::resource('documents', DocumentController::class);
    Route::get('/whatsapp', [WhatsappConnectionController::class, 'show'])->middleware('can:admin')->name('whatsapp.show');
    Route::post('/whatsapp/qr', [WhatsappConnectionController::class, 'qr'])->middleware('can:admin')->name('whatsapp.qr');
    Route::post('/whatsapp/webhook', [WhatsappConnectionController::class, 'configureWebhook'])->middleware('can:admin')->name('whatsapp.webhook');
    Route::post('/whatsapp/send', [WhatsappConnectionController::class, 'send'])->middleware('can:admin')->name('whatsapp.send');
    Route::resource('admin/users', UserController::class)->middleware('can:admin')->names('admin.users')->except(['show']);
});

Route::post('/webhooks/evolution/{event?}', EvolutionWebhookController::class)
    ->withoutMiddleware([PreventRequestForgery::class])
    ->where('event', '.*')
    ->name('webhooks.evolution');
