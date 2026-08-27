<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('lang/{locale}', [LocaleController::class, 'switch'])->name('lang.switch');


Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', function () {
    if (auth()->user()->isClient()) {
        return redirect()->route('client.dashboard');
    }

    $revenuePerCurrency = \App\Models\Payment::where('status', 'completed')
        ->selectRaw('currency, sum(amount) as total')
        ->groupBy('currency')
        ->pluck('total', 'currency')
        ->toArray();

    $totalOrderValue = \App\Models\Order::selectRaw('currency, sum(price) as total')
        ->groupBy('currency')
        ->pluck('total', 'currency');

    $pendingRevenuePerCurrency = \App\Models\Order::all()
        ->groupBy('currency')
        ->map(fn($orders) => $orders->sum('pending_amount'))
        ->filter(fn($amount) => $amount > 0)
        ->toArray();

    $dueDateKpi = function ($query) {
        return [
            'count' => (clone $query)->count(),
            'amount_per_currency' => (clone $query)
                ->selectRaw('currency, sum(renewal_price) as total')
                ->groupBy('currency')
                ->pluck('total', 'currency')
                ->toArray(),
        ];
    };

    $dueDates = [
        'today' => $dueDateKpi(\App\Models\Order::dueToday()),
        'next7' => $dueDateKpi(\App\Models\Order::dueWithin(7)),
        'next30' => $dueDateKpi(\App\Models\Order::dueWithin(30)),
        'overdue' => $dueDateKpi(\App\Models\Order::overdueRenewals()),
    ];

    $upcomingDueDates = \App\Models\Order::renewable()
        ->with(['client', 'product'])
        ->orderBy('next_due_date')
        ->limit(10)
        ->get();

    $pendingPaymentsQuery = \App\Models\Payment::where('status', 'pending');
    $pendingPaymentsKpi = [
        'count' => (clone $pendingPaymentsQuery)->count(),
        'amount_per_currency' => (clone $pendingPaymentsQuery)
            ->selectRaw('currency, sum(amount) as total')
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->toArray(),
        'old_count' => (clone $pendingPaymentsQuery)->where('created_at', '<=', now()->subDays(3))->count(),
    ];

    return view('dashboard', [
        'clientsCount' => \App\Models\Client::count(),
        'ordersCount' => \App\Models\Order::count(),
        'revenuePerCurrency' => $revenuePerCurrency,
        'pendingRevenuePerCurrency' => $pendingRevenuePerCurrency,
        'clientCreditPerCurrency' => \App\Models\Client::where('credit_balance', '>', 0)
            ->selectRaw('currency, sum(credit_balance) as total')
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->toArray(),
        'activeProductsCount' => \App\Models\Order::active()->count(),
        'expiringSoonCount' => \App\Models\Order::expiringSoon()->count(),
        'expiredProductsCount' => \App\Models\Order::expired()->count(),
        'reminders' => \App\Models\Order::expiringSoon()
            ->with(['client'])
            ->limit(5)
            ->get()
            ->map(fn($o) => [
                'type' => 'expiring',
                'order_id' => $o->id,
                'client_name' => $o->client->name,
                'product_name' => $o->product->name,
                'days' => now()->diffInDays($o->expiry_date, false),
            ]),
        'recentActivity' => [], // Placeholder for now
        'dueDates' => $dueDates,
        'upcomingDueDates' => $upcomingDueDates,
        'pendingPaymentsKpi' => $pendingPaymentsKpi,
    ]);
})->middleware(['auth', 'verified', 'admin'])->name('dashboard');

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
    Route::get('/payments/{payment}/edit', \App\Livewire\Payments\PaymentForm::class)->name('payments.edit');

    // Warranty Claims
    Route::get('/claims', \App\Livewire\WarrantyClaims\ClaimIndex::class)->name('claims.index');

    // Due Dates (renewals)
    Route::get('/due-dates', \App\Livewire\DueDates\DueDateIndex::class)->name('due-dates.index');
    Route::get('/due-dates/calendar', \App\Livewire\DueDates\DueDateCalendar::class)->name('due-dates.calendar');
});

require __DIR__.'/auth.php';
