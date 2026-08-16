<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\ContactCardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MateController;
use App\Http\Controllers\MeetController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\RsvpController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/c/{community}/{event}', [EventController::class, 'show'])->name('events.show');
Route::get('/card/{token}', [ContactCardController::class, 'show'])->name('cards.show');
Route::get('/card/{token}/vcard', [ContactCardController::class, 'vcard'])->name('cards.vcard');
Route::redirect('/admin/login', '/login')->name('admin.login');

Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/onboarding', [OnboardingController::class, 'edit'])->name('onboarding.edit');
    Route::put('/onboarding', [OnboardingController::class, 'update'])->name('onboarding.update');

    Route::post('/c/{community}/{event}/rsvp', [RsvpController::class, 'store'])->name('events.rsvp');
    Route::get('/c/{community}/{event}/checkout', [RsvpController::class, 'localCheckout'])->name('events.checkout.local');
    Route::post('/c/{community}/{event}/checkout', [RsvpController::class, 'confirmLocal'])->name('events.checkout.confirm');
    Route::get('/c/{community}/{event}/success', [RsvpController::class, 'success'])->name('events.checkout.success');

    Route::get('/c/{community}/{event}/chat', [EventController::class, 'chat'])->name('events.chat');
    Route::post('/c/{community}/{event}/chat', [EventController::class, 'storeMessage'])->name('events.chat.store');

    Route::get('/c/{community}/{event}/meet', [MeetController::class, 'show'])->name('events.meet');
    Route::post('/c/{community}/{event}/meet', [MeetController::class, 'store'])->name('events.meet.store');

    Route::get('/mates', [MateController::class, 'index'])->name('mates.index');
    Route::post('/mates/{mate}/share', [MateController::class, 'share'])->name('mates.share');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/events/new', [AdminEventController::class, 'create'])->name('events.create');
    Route::post('/events', [AdminEventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}', [AdminEventController::class, 'show'])->name('events.show');
    Route::get('/events/{event}/edit', [AdminEventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [AdminEventController::class, 'update'])->name('events.update');
    Route::post('/events/{event}/attendance', [AdminEventController::class, 'markAttendance'])->name('events.attendance');
});

Route::redirect('/organizer', '/admin');
Route::redirect('/organizer/events/new', '/admin/events/new');

require __DIR__.'/settings.php';
