<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('lang/{locale}', [LocaleController::class, 'switch'])->name('lang.switch');


Route::get('/', function () {
    return view('welcome');
})->name('home');

// ── Fully public pages — never behind auth, never redirect to login ────────
Route::get('/payment-methods', function () {
    return view('public.payment-methods', [
        'methodsByCategory' => \App\Models\PaymentMethod::publicReady()->groupBy('category'),
        'categoryLabels' => \App\Models\PaymentMethod::CATEGORIES,
    ]);
})->name('public.payment-methods');

Route::get('/about', function () {
    return view('public.about');
})->name('public.about');

Route::get('/terms', function () {
    return view('public.terms');
})->name('public.terms');

Route::get('/dashboard', \App\Livewire\Dashboard\DashboardOverview::class)
    ->middleware(['auth', 'verified', 'admin'])
    ->name('dashboard');

// Client Portal Routes
Route::middleware(['auth', 'verified', 'client'])->prefix('portal')->name('client.')->group(function () {
    Route::get('/', \App\Livewire\ClientPortal\PortalDashboard::class)->name('dashboard');
    Route::get('/products', \App\Livewire\ClientPortal\PortalProducts::class)->name('products.index');
    Route::get('/products/{order}', \App\Livewire\ClientPortal\PortalProductShow::class)->name('products.show');
    Route::get('/payments', \App\Livewire\ClientPortal\PortalPayments::class)->name('payments.index');
    Route::get('/payment-methods', \App\Livewire\ClientPortal\PaymentMethods::class)->name('payment-methods');
    Route::get('/about', \App\Livewire\ClientPortal\About::class)->name('about');
    Route::get('/contact', \App\Livewire\ClientPortal\Contact::class)->name('contact');
});

// Admin ONLY Routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Client Routes
    Route::get('/clients', \App\Livewire\Clients\ClientIndex::class)->name('clients.index');
    Route::get('/clients/create', \App\Livewire\Clients\ClientForm::class)->name('clients.create');
    Route::get('/clients/unpaid', \App\Livewire\Clients\ClientUnpaidIndex::class)->name('clients.unpaid');
    Route::get('/clients/{client}', \App\Livewire\Clients\ClientShow::class)->name('clients.show');
    Route::get('/clients/{client}/edit', \App\Livewire\Clients\ClientForm::class)->name('clients.edit');
    Route::get('/clients/{client}/transactions', \App\Livewire\Clients\ClientTransactions::class)->name('clients.transactions');

    // Order Routes
    Route::get('/orders', \App\Livewire\Orders\OrderIndex::class)->name('orders.index');
    Route::get('/orders/create', \App\Livewire\Orders\OrderForm::class)->name('orders.create');
    Route::get('/orders/{order}/edit', \App\Livewire\Orders\OrderForm::class)->name('orders.edit');

    // Product Routing (Catalog)
    Route::get('/products', \App\Livewire\Products\ProductIndex::class)->name('products.index');
    Route::get('/products/create', \App\Livewire\Products\ProductForm::class)->name('products.create');
    Route::get('/products/{product}', \App\Livewire\Products\ProductShow::class)->name('products.show');
    Route::get('/products/{product}/edit', \App\Livewire\Products\ProductForm::class)->name('products.edit');
    Route::get('/products/{product}/fields', \App\Livewire\Products\ProductFields::class)->name('products.fields');

    // Payment Routes
    Route::get('/payments', \App\Livewire\Payments\PaymentIndex::class)->name('payments.index');
    Route::get('/payments/create', \App\Livewire\Payments\PaymentForm::class)->name('payments.create');
    Route::get('/payments/pending', \App\Livewire\Payments\PendingPaymentIndex::class)->name('payments.pending');
    Route::get('/payments/methods', \App\Livewire\Payments\PaymentMethodIndex::class)->name('payments.methods');
    Route::get('/payments/methods/create', \App\Livewire\Payments\PaymentMethodForm::class)->name('payments.methods.create');
    Route::get('/payments/methods/{paymentMethod}/edit', \App\Livewire\Payments\PaymentMethodForm::class)->name('payments.methods.edit');
    Route::get('/payments/{payment}/edit', \App\Livewire\Payments\PaymentForm::class)->name('payments.edit');

    // Warranty Claims
    Route::get('/claims', \App\Livewire\WarrantyClaims\ClaimIndex::class)->name('claims.index');

    // Due Dates (renewals)
    Route::get('/due-dates', \App\Livewire\DueDates\DueDateIndex::class)->name('due-dates.index');
    Route::get('/due-dates/calendar', \App\Livewire\DueDates\DueDateCalendar::class)->name('due-dates.calendar');
});

require __DIR__.'/auth.php';
