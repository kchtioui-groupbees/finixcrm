<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name', 'email', 'finix_email', 'status', 'phone', 'notes', 'tags', 'user_id', 'credit_balance', 'currency'
    ];

    protected $casts = [
        'tags' => 'array',
        'credit_balance' => 'decimal:3',
    ];

    /**
     * Memoised balance_breakdown for this instance — see the accessor. Not
     * persisted or serialised; a fresh()/refresh() gives a clean instance.
     *
     * @var array<string,float>|null
     */
    private ?array $balanceBreakdownCache = null;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }

    public function balanceTransactions()
    {
        return $this->hasMany(ClientBalanceTransaction::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function warrantyClaims()
    {
        return $this->hasManyThrough(WarrantyClaim::class, Order::class);
    }

    public function getTotalPaidAttribute()
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    public function getTotalPendingAttribute()
    {
        return $this->orders->sum(fn($order) => $order->pending_amount);
    }

    public function getActiveOrdersCountAttribute()
    {
        return $this->orders()->whereIn('status', ['active', 'completed'])->count();
    }

    public function getExpiredOrdersCountAttribute()
    {
        return $this->orders()->where('status', 'expired')->count();
    }

    public function getTotalOrdersCountAttribute()
    {
        return $this->orders()->count();
    }

    public function getTotalOrdersAmountAttribute()
    {
        return (float) $this->orders()->sum('price');
    }

    public function getWarrantyActiveCountAttribute()
    {
        return $this->orders()
            ->where('warranty_enabled', true)
            ->whereDate('warranty_end_date', '>=', now())
            ->count();
    }

    public function getWarrantyExpiredCountAttribute()
    {
        return $this->orders()
            ->where('warranty_enabled', true)
            ->whereDate('warranty_end_date', '<', now())
            ->count();
    }

    /**
     * A wa.me link for this client's phone. Tunisian numbers are stored
     * locally without the country code, so an 8-digit number gets '216'
     * prepended; numbers already carrying a country code pass through.
     */
    public function getWhatsappUrlAttribute(): ?string
    {
        if (!$this->phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $this->phone);

        if (strlen($digits) <= 8) {
            $digits = '216' . $digits;
        }

        return "https://wa.me/{$digits}";
    }

    /**
     * Total cashback ever earned by this client (ledger 'cashback_reward'
     * entries), regardless of whether it has since been used.
     */
    public function getCashbackEarnedAttribute(): float
    {
        return (float) $this->balanceTransactions()
            ->where('type', 'cashback_reward')
            ->sum('amount');
    }

    /**
     * How much of the cashback this client earned has since been spent.
     *
     * Derived from balance_breakdown, which attributes each debit to the
     * oldest credit still standing, so this answers the question exactly
     * rather than approximating it.
     *
     * It used to be min(cashback earned, every debit ever made), on the
     * grounds that the ledger is one shared pool. That reading breaks now
     * that overpayment credit is auto-appliable: sweeping overpayment writes
     * a 'usage' row, which the old formula counted as cashback being spent —
     * so a client who earned 50 of cashback and had 100 of overpayment swept
     * was shown 0 cashback available despite never having spent any of it.
     */
    public function getCashbackUsedAttribute(): float
    {
        return max(0.0, $this->cashback_earned - $this->cashback_available);
    }

    public function getCashbackAvailableAttribute(): float
    {
        $remaining = $this->balance_breakdown['cashback_reward'] ?? 0.0;

        return max(0.0, min($remaining, $this->cashback_earned));
    }

    /**
     * Cashback still "en attente" — earned on an order snapshot but not
     * yet granted to the ledger (order not fully paid yet, so
     * CashbackRewardService hasn't rewarded it). This is structurally
     * disjoint from cashback_available: an amount here has no ledger
     * transaction yet, so it can never simultaneously appear as available.
     */
    public function getCashbackPendingAttribute(): float
    {
        return (float) $this->orders()
            ->where('cashback_enabled_snapshot', true)
            ->where('cashback_rewarded', false)
            ->where('cashback_reversed', false)
            ->where('cashback_amount', '>', 0)
            ->sum('cashback_amount');
    }

    /**
     * The still-available balance split by the credit source it came from.
     *
     * The ledger is one shared pool — a debit row does not record which
     * credit it drew from — so the split is attributed oldest-credit-first:
     * credits are queued in chronological order and the client's total
     * debits are drawn off the front of that queue. What is left is what
     * they can still spend, and it sums back to credit_balance exactly.
     *
     * Debits are pooled rather than applied at their own timestamp on
     * purpose. reallocateForClient() deletes and recreates every
     * 'overpayment' row on each run, so those rows always carry the newest
     * timestamps even though that money came in first; charging each debit
     * at its own instant would strand it against a momentarily empty queue
     * and leave the remainders summing to far more than credit_balance
     * (observed on live data: 252.780 reported against a real 45.530).
     *
     * Pending cashback is absent by construction: it has no ledger row
     * until its order is fully paid.
     *
     * @return array<string,float> type => remaining amount, positive only
     */
    public function getBalanceBreakdownAttribute(): array
    {
        // Memoised per instance: cashback_available, the hold reasons and
        // both UI surfaces all read this, and each call is a full ledger
        // fetch. Cleared by refresh()/fresh() like any other loaded state.
        if ($this->balanceBreakdownCache !== null) {
            return $this->balanceBreakdownCache;
        }

        // Seeded and back-dated rows can share a created_at, so id breaks
        // the tie — otherwise the attribution would not be reproducible.
        $transactions = $this->balanceTransactions()
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id', 'type', 'amount']);

        $queue = [];  // credits not yet spent, oldest first
        $debt  = 0.0; // every negative row, whatever its type: a 'usage' and a
                      // negative manual_adjustment both take real money out.

        foreach ($transactions as $transaction) {
            $amount = (float) $transaction->amount;

            if ($amount > 0) {
                $queue[] = ['type' => $transaction->type, 'amount' => $amount];
            } else {
                $debt += abs($amount);
            }
        }

        foreach ($queue as $key => $credit) {
            if ($debt <= 0.0001) {
                break;
            }

            $take = min($credit['amount'], $debt);
            $queue[$key]['amount'] -= $take;
            $debt -= $take;
        }

        $precision = $this->currencyPrecision();
        $breakdown = [];

        foreach ($queue as $credit) {
            $breakdown[$credit['type']] = ($breakdown[$credit['type']] ?? 0.0) + $credit['amount'];
        }

        // Types whose remainder rounds away are dropped rather than shown
        // as a zero line. An over-spent ledger (impossible in practice)
        // drains the queue entirely, so this can never return a negative.
        $breakdown = array_filter(
            array_map(fn ($amount) => round($amount, $precision), $breakdown),
            fn ($amount) => $amount > 0
        );

        // Rounding each type in isolation can drift off credit_balance (two
        // 0.005 rows on a 2-decimal currency would each round to 0.01 and
        // display double the real balance). Settle any residual on the
        // largest line so the parts always add up to the headline figure.
        if ($breakdown !== []) {
            $residual = round((float) $this->credit_balance - array_sum($breakdown), $precision);

            if (abs($residual) > 0) {
                $largest = array_search(max($breakdown), $breakdown, true);
                $breakdown[$largest] = round($breakdown[$largest] + $residual, $precision);
                $breakdown = array_filter($breakdown, fn ($amount) => $amount > 0);
            }
        }

        return $this->balanceBreakdownCache = $breakdown;
    }

    /**
     * The part of balance_breakdown the "auto-apply to unpaid orders"
     * feature is allowed to draw from (an admin setting).
     *
     * @return array<string,float>
     */
    public function getAutoApplicableBreakdownAttribute(): array
    {
        $allowedTypes = app(\App\Services\FinixBalanceAutoApplyService::class)->allowedTypes();

        if (empty($allowedTypes)) {
            return [];
        }

        // Show the allowed slice of the breakdown, but never more in total
        // than auto_apply_eligible_balance actually permits — that figure is
        // deliberately the stricter of the two (see below), so the display
        // is trimmed to it oldest-type-first rather than the other way round.
        $budget = $this->auto_apply_eligible_balance;
        $precision = $this->currencyPrecision();
        $applicable = [];

        foreach ($this->balance_breakdown as $type => $amount) {
            if (!in_array($type, $allowedTypes, true)) {
                continue;
            }

            if ($budget <= 0.0001) {
                break;
            }

            $take = min($amount, $budget);
            $budget -= $take;
            $applicable[$type] = round($take, $precision);
        }

        return array_filter($applicable, fn ($amount) => $amount > 0);
    }

    /**
     * Available balance restricted to the credit source types auto-apply
     * may draw from — never includes pending cashback (which isn't in the
     * ledger at all yet) and never exceeds the client's true total
     * available balance.
     *
     * Deliberately conservative: every debit the client has ever made is
     * charged against the allowed-type earnings, rather than attributed to
     * whichever credit happens to be oldest. That understates the figure
     * when a non-allowed credit type is present, and it must stay that way.
     * Attributing debits oldest-first here would let a single call sweep a
     * forbidden credit type onto an order: each pass re-reads the ledger,
     * re-attributes the debit it just wrote to the older, forbidden credit,
     * and frees the allowed credit up to be spent again — draining the whole
     * balance in a few passes. Charging every debit to the allowed pool
     * makes that impossible by construction.
     *
     * When every ledger type is allowed (the default) this equals
     * credit_balance exactly, so the caution costs nothing in normal use.
     */
    public function getAutoApplyEligibleBalanceAttribute(): float
    {
        $allowedTypes = app(\App\Services\FinixBalanceAutoApplyService::class)->allowedTypes();

        if (empty($allowedTypes)) {
            return 0.0;
        }

        $allowedEarned = (float) $this->balanceTransactions()
            ->whereIn('type', $allowedTypes)
            ->where('amount', '>', 0)
            ->sum('amount');

        $totalDebits = abs((float) $this->balanceTransactions()
            ->where('amount', '<', 0)
            ->sum('amount'));

        $eligible = round($allowedEarned - $totalDebits, $this->currencyPrecision());

        return max(0.0, min($eligible, (float) $this->credit_balance));
    }

    /**
     * Why money is sitting in the balance instead of being applied right
     * now, in plain language the client can act on. Empty when everything
     * available is on its way to an unpaid order.
     *
     * @return list<array{code:string,amount:?float,message:string,type:?string}>
     */
    public function getBalanceHoldReasonsAttribute(): array
    {
        $service = app(\App\Services\FinixBalanceAutoApplyService::class);
        $allowedTypes = $service->allowedTypes();
        $precision = $this->currencyPrecision();
        $eligible = $this->auto_apply_eligible_balance;

        $reasons = [];

        // Both switches stop the sweep dead (the service returns early on
        // either), so both have to count as "disabled" here — otherwise a
        // client with money, an unpaid order and auto_apply_to_old_orders
        // off is shown no explanation at all, which is the exact blind spot
        // this whole accessor exists to close.
        $disabled = !$service->isEnabled() || !$service->appliesToOldUnpaidOrders();

        if ($disabled) {
            $reasons[] = [
                'code' => 'feature_disabled',
                'amount' => $eligible,
                'type' => null,
                'message' => __('Automatic application is currently disabled by the administrator.'),
            ];
        }

        foreach ($this->balance_breakdown as $type => $amount) {
            if (in_array($type, $allowedTypes, true)) {
                continue;
            }

            $reasons[] = [
                'code' => 'type_not_allowed',
                'amount' => $amount,
                'type' => $type,
                'message' => __(':amount of :type credit is not eligible for automatic application under the current settings.', [
                    'amount' => $this->formatAmount($amount),
                    'type' => __(\App\Services\FinixBalanceAutoApplyService::CREDIT_TYPE_LABELS[$type] ?? $type),
                ]),
            ];
        }

        $cashbackPending = $this->cashback_pending;

        if ($cashbackPending > 0) {
            $reasons[] = [
                'code' => 'cashback_pending',
                'amount' => $cashbackPending,
                'type' => null,
                'message' => __(':amount of cashback is still pending and will only become available once the related order is fully paid.', [
                    'amount' => $this->formatAmount($cashbackPending),
                ]),
            ];
        }

        // Both messages below promise an automatic application, so neither
        // may appear while the feature is switched off — the list would
        // otherwise say "disabled by the administrator" and "will be applied
        // automatically" one line under the other.
        if (!$disabled && $eligible > 0.0001) {
            // pending_amount is a PHP accessor, not a column, so the orders
            // have to be walked rather than summed in SQL — with the same
            // threshold the auto-apply service uses to call an order unpaid,
            // so the two branches below can never both be true.
            $totalDue = round(
                $this->orders
                    ->filter(fn ($order) => $order->pending_amount > 0.0001)
                    ->sum(fn ($order) => $order->pending_amount),
                $precision
            );

            if ($totalDue <= 0.0001) {
                $reasons[] = [
                    'code' => 'no_unpaid_order',
                    'amount' => $eligible,
                    'type' => null,
                    'message' => __(':amount is available and will be applied automatically to your next unpaid order.', [
                        'amount' => $this->formatAmount($eligible),
                    ]),
                ];
            } elseif ($eligible - $totalDue > 0.0001) {
                $leftover = round($eligible - $totalDue, $precision);

                $reasons[] = [
                    'code' => 'exceeds_amount_due',
                    'amount' => $leftover,
                    'type' => null,
                    'message' => __(':amount will remain available after covering everything currently due.', [
                        'amount' => $this->formatAmount($leftover),
                    ]),
                ];
            }
        }

        return $reasons;
    }

    /**
     * Total ever applied to this client's orders by
     * FinixBalanceAutoApplyService — identified the same way the UI badge
     * and the reversal guard identify an automatic application: a 'usage'
     * transaction with created_by null (never an admin's own
     * applyCredit(), which always stamps created_by).
     */
    public function getAutoAppliedTotalAttribute(): float
    {
        return abs((float) $this->balanceTransactions()
            ->where('type', 'usage')
            ->whereNull('created_by')
            ->sum('amount'));
    }

    /**
     * Best-effort "last activity": the most recent of account login, order,
     * or payment. Null if the client has never done anything yet.
     */
    public function getLastActivityAtAttribute(): ?\Carbon\Carbon
    {
        $dates = collect([
            $this->user?->last_login_at,
            $this->orders()->max('created_at'),
            $this->payments()->max('created_at'),
        ])->filter()->map(fn ($d) => \Carbon\Carbon::parse($d));

        return $dates->isEmpty() ? null : $dates->max();
    }

    /**
     * Recalculate and update the cached credit balance
     */
    public function refreshBalance()
    {
        $this->balanceBreakdownCache = null;
        $this->credit_balance = $this->balanceTransactions()->sum('amount');
        $this->save();
        return $this->credit_balance;
    }

    /**
     * Reloading the row has to drop the memoised breakdown with it, or a
     * caller that mutates the ledger and then refresh()es would keep reading
     * the pre-mutation attribution.
     */
    public function refresh()
    {
        $this->balanceBreakdownCache = null;

        return parent::refresh();
    }

    /** TND is quoted to the millime (3 decimals); every other currency here uses 2. */
    private function currencyPrecision(): int
    {
        return $this->currency === 'TND' ? 3 : 2;
    }

    public function formatAmount($amount)
    {
        $decimals = ($this->currency === 'TND') ? 3 : 2;
        $symbol = match($this->currency) {
            'USD' => '$',
            'EUR' => '€',
            'TND' => 'TND ',
            default => $this->currency . ' '
        };

        return $symbol . number_format($amount, $decimals);
    }

    public function getCurrencySymbolAttribute()
    {
        return match($this->currency) {
            'USD' => '$',
            'EUR' => '€',
            'TND' => 'TND',
            default => $this->currency
        };
    }
}
