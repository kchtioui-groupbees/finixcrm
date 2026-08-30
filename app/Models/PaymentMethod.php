<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentMethod extends Model
{
    public const CATEGORIES = [
        'wallet' => 'Wallet',
        'bank_transfer' => 'Virement bancaire',
        'postal_transfer' => 'Virement postal',
        'agency' => 'Paiement en agence',
        'card' => 'Carte bancaire',
        'gateway' => 'Gateway',
        'crypto' => 'Cryptomonnaie',
        'cash' => 'Espèces',
        'other' => 'Autre',
    ];

    public const FEE_TYPES = ['none', 'fixed', 'percentage', 'unknown'];

    public const FEE_PAID_BY = ['customer', 'merchant', 'none'];

    public const UNKNOWN_FEE_LABEL = 'Les éventuels frais de paiement sont à la charge du client.';

    protected $fillable = [
        'key', 'label', 'category', 'description', 'logo_path', 'instructions',
        'currencies', 'requires_confirmation', 'proof_required', 'reference_required',
        'is_active', 'is_public', 'sort_order', 'archived_at',
        'fee_type', 'fee_value', 'fee_currency', 'fee_paid_by', 'fee_label',
        'details',
    ];

    protected $casts = [
        'requires_confirmation' => 'boolean',
        'proof_required' => 'boolean',
        'reference_required' => 'boolean',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'sort_order' => 'integer',
        'archived_at' => 'datetime',
        'currencies' => 'array',
        'fee_value' => 'decimal:3',
        'details' => 'array',
    ];

    public function fields()
    {
        return $this->hasMany(PaymentMethodField::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeNotArchived($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Ready to show on the public payment-methods page: active, public,
     * not archived, given a category (a method still sitting at
     * category=null has never been reviewed through the payment-method
     * form and is not "correctly configured", regardless of its is_public
     * default), and — for categories that need contact/account details to
     * be usable — actually configured with at least one non-empty public
     * field. A method with no fields required at all (e.g. cash) is
     * trivially "configured".
     */
    public function isPublicReady(): bool
    {
        if (!$this->is_active || !$this->is_public || $this->archived_at || !$this->category) {
            return false;
        }

        if (!in_array($this->category, ['bank_transfer', 'postal_transfer', 'crypto', 'wallet', 'agency'], true)) {
            return true;
        }

        return $this->fields()->public()->configured()->exists();
    }

    /**
     * All methods actually ready to display on the public page, in display
     * order. This is a terminal query (not a chainable scope) since
     * readiness also depends on related field data.
     */
    public static function publicReady()
    {
        return static::active()->public()->notArchived()
            ->with(['fields' => fn ($q) => $q->public()->orderBy('sort_order')])
            ->get()
            ->filter->isPublicReady()
            ->values();
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /**
     * Human-readable fee summary. Never renders "0" for a genuinely unknown
     * fee — the fee_label (e.g. "Les éventuels frais de paiement sont à la
     * charge du client.") is shown instead. A fee is only ever computed/
     * displayed as a real amount once a value has actually been configured.
     */
    public function getFeeSummaryAttribute(): string
    {
        return match ($this->fee_type) {
            'none' => __('No fee'),
            'percentage' => $this->fee_value !== null
                ? rtrim(rtrim((string) $this->fee_value, '0'), '.') . '%'
                : __('Not configured'),
            'fixed' => $this->fee_value !== null
                ? rtrim(rtrim((string) $this->fee_value, '0'), '.') . ' ' . ($this->fee_currency ?? $this->currencies[0] ?? '')
                : __('Not configured'),
            default => $this->fee_label ?: self::UNKNOWN_FEE_LABEL,
        };
    }

    public function getFeePaidByLabelAttribute(): string
    {
        return match ($this->fee_paid_by) {
            'customer' => __('Customer'),
            'merchant' => __('Finix'),
            default => __('None'),
        };
    }

    /**
     * Duplicate this method (and its fields) under a new unique key, so an
     * admin can start a new method from an existing one without ever
     * touching the original. The clone is created inactive so it can't
     * accidentally go live before being reviewed.
     */
    public function duplicate(): self
    {
        $baseKey = $this->key . '_copy';
        $key = $baseKey;
        $i = 1;
        while (static::where('key', $key)->exists()) {
            $key = $baseKey . '_' . (++$i);
        }

        $clone = $this->replicate(['key']);
        $clone->key = $key;
        $clone->label = $this->label . ' (copie)';
        $clone->is_active = false;
        $clone->archived_at = null;
        $clone->save();

        foreach ($this->fields as $field) {
            $clone->fields()->create($field->only(['label', 'value', 'type', 'is_public', 'copyable', 'sort_order']));
        }

        return $clone;
    }

    public static function generateUniqueKey(string $label): string
    {
        $base = Str::slug($label, '_');
        $key = $base;
        $i = 1;
        while (static::where('key', $key)->exists()) {
            $key = $base . '_' . (++$i);
        }

        return $key;
    }

    /**
     * Whether a payment logged under this method key needs manual
     * confirmation before it has any financial effect. Defaults to true
     * (fail-safe) for a key with no matching configuration row.
     */
    public static function requiresConfirmation(?string $key): bool
    {
        if (!$key) {
            return true;
        }

        $value = static::where('key', $key)->value('requires_confirmation');

        return $value === null ? true : (bool) $value;
    }
}
