<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

/**
 * Idempotent: safe to run any number of times. Each method is upserted by
 * its unique `key`, so re-running never creates duplicates — it just
 * converges every listed method's configuration (label, category, fees,
 * currencies...) back to this canonical baseline.
 *
 * Contact/account fields are handled separately and more conservatively:
 * they are only ever seeded for a method that currently has *no* fields at
 * all. This means re-running the seeder — or running it after the
 * backfill migration already populated fields from legacy data, or after
 * an admin has customized a method through the UI — never overwrites or
 * duplicates anything an admin has configured.
 *
 * Rule for unknown fees: never store an unknown fee as 0. Unknown fees use
 * fee_type=unknown, fee_value=null, fee_paid_by=customer, and a fee_label
 * explaining the fee is charged to the customer if any applies.
 *
 * Rule for account/wallet details: never invent a RIB or wallet address.
 * Seeded values are only ever the real, known contact info from the
 * business — everything else (RIB, wallet address...) starts null and is
 * only ever filled in from the admin UI.
 */
class PaymentMethodSeeder extends Seeder
{
    private const UNKNOWN_FEE_LABEL = PaymentMethod::UNKNOWN_FEE_LABEL;

    public function run(): void
    {
        foreach ($this->methods() as $definition) {
            $fields = $definition['fields'] ?? [];
            unset($definition['fields']);

            $method = PaymentMethod::updateOrCreate(
                ['key' => $definition['key']],
                $definition
            );

            if ($method->fields()->count() > 0) {
                continue; // already has contact/account data — never overwrite it
            }

            foreach ($fields as $order => $field) {
                $method->fields()->create(array_merge($field, ['sort_order' => $order]));
            }
        }
    }

    private function methods(): array
    {
        return [
            // ── 1. D17 ──────────────────────────────────────────────────
            [
                'key' => 'd17',
                'label' => 'D17',
                'category' => 'wallet',
                'currencies' => ['TND'],
                'is_public' => true,
                'requires_confirmation' => true,
                'is_active' => true,
                'sort_order' => 10,
                'fee_type' => 'percentage',
                'fee_value' => 1,
                'fee_currency' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => null,
                'fields' => [
                    ['label' => 'Numéro de téléphone 1', 'value' => '92 871 752', 'type' => 'phone', 'is_public' => true, 'copyable' => true],
                    ['label' => 'Numéro de téléphone 2', 'value' => '25 208 023', 'type' => 'phone', 'is_public' => true, 'copyable' => true],
                ],
            ],

            // ── 2. Flouci ───────────────────────────────────────────────
            [
                'key' => 'flouci',
                'label' => 'Flouci',
                'category' => 'wallet',
                'currencies' => ['TND'],
                'is_public' => true,
                'requires_confirmation' => true,
                'is_active' => true,
                'sort_order' => 20,
                'fee_type' => 'unknown',
                'fee_value' => null,
                'fee_currency' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => self::UNKNOWN_FEE_LABEL,
                'fields' => [
                    ['label' => 'Titulaire 1', 'value' => 'Khaled Chtioui', 'type' => 'text', 'is_public' => true, 'copyable' => false],
                    ['label' => 'Numéro de téléphone 1', 'value' => '92 871 752', 'type' => 'phone', 'is_public' => true, 'copyable' => true],
                    ['label' => 'Titulaire 2', 'value' => 'Dhia Boubaker', 'type' => 'text', 'is_public' => true, 'copyable' => false],
                    ['label' => 'Numéro de téléphone 2', 'value' => '25 208 023', 'type' => 'phone', 'is_public' => true, 'copyable' => true],
                ],
            ],

            // ── 3. WafaCash ─────────────────────────────────────────────
            [
                'key' => 'wafacash',
                'label' => 'WafaCash',
                'category' => 'agency',
                'currencies' => ['TND'],
                'is_public' => true,
                'requires_confirmation' => true,
                'is_active' => true,
                'sort_order' => 30,
                'fee_type' => 'unknown',
                'fee_value' => null,
                'fee_currency' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => self::UNKNOWN_FEE_LABEL,
                'fields' => [
                    ['label' => 'Titulaire', 'value' => 'Khaled Chtioui', 'type' => 'text', 'is_public' => true, 'copyable' => false],
                    ['label' => 'Numéro de téléphone', 'value' => '92 871 752', 'type' => 'phone', 'is_public' => true, 'copyable' => true],
                    ['label' => 'Agence', 'value' => 'Agence Jemmel', 'type' => 'text', 'is_public' => true, 'copyable' => false],
                ],
            ],

            // ── 4. IZI Zitouna ──────────────────────────────────────────
            [
                'key' => 'izi_zitouna',
                'label' => 'IZI Zitouna',
                'category' => 'agency',
                'currencies' => ['TND'],
                'is_public' => true,
                'requires_confirmation' => true,
                'is_active' => true,
                'sort_order' => 40,
                'fee_type' => 'unknown',
                'fee_value' => null,
                'fee_currency' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => self::UNKNOWN_FEE_LABEL,
                'fields' => [
                    ['label' => 'Titulaire', 'value' => 'Khaled Chtioui', 'type' => 'text', 'is_public' => true, 'copyable' => false],
                    ['label' => 'Numéro de téléphone', 'value' => '92 871 752', 'type' => 'phone', 'is_public' => true, 'copyable' => true],
                    ['label' => 'Agence', 'value' => 'Agence Jemmel', 'type' => 'text', 'is_public' => true, 'copyable' => false],
                ],
            ],

            // ── 5. Kashy ────────────────────────────────────────────────
            [
                'key' => 'kashy',
                'label' => 'Kashy',
                'category' => 'wallet',
                'currencies' => ['TND'],
                'is_public' => true,
                'requires_confirmation' => true,
                'is_active' => true,
                'sort_order' => 50,
                'fee_type' => 'unknown',
                'fee_value' => null,
                'fee_currency' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => self::UNKNOWN_FEE_LABEL,
                'fields' => [
                    ['label' => 'Titulaire', 'value' => 'Khaled Chtioui', 'type' => 'text', 'is_public' => true, 'copyable' => false],
                    ['label' => 'Numéro de téléphone', 'value' => '92 871 752', 'type' => 'phone', 'is_public' => true, 'copyable' => true],
                ],
            ],

            // ── 6. Virement bancaire / RIB ──────────────────────────────
            [
                'key' => 'virement_bancaire',
                'label' => 'Virement Bancaire',
                'category' => 'bank_transfer',
                'currencies' => ['TND', 'EUR', 'USD'],
                'is_public' => true,
                'requires_confirmation' => true,
                'is_active' => true,
                'sort_order' => 60,
                'fee_type' => 'fixed',
                'fee_value' => 2,
                'fee_currency' => 'TND',
                'fee_paid_by' => 'customer',
                'fee_label' => null,
                // Bank account(s) are never invented — added from the admin UI.
                'fields' => [
                    ['label' => 'Titulaire du compte', 'value' => null, 'type' => 'text', 'is_public' => true, 'copyable' => true],
                    ['label' => 'Nom de la banque', 'value' => null, 'type' => 'text', 'is_public' => true, 'copyable' => false],
                    ['label' => 'RIB', 'value' => null, 'type' => 'text', 'is_public' => true, 'copyable' => true],
                ],
            ],

            // ── 7. Virement postal / RIB postal ─────────────────────────
            [
                'key' => 'virement_postal',
                'label' => 'Virement Postal',
                'category' => 'postal_transfer',
                'currencies' => ['TND'],
                'is_public' => true,
                'requires_confirmation' => true,
                'is_active' => true,
                'sort_order' => 70,
                'fee_type' => 'unknown',
                'fee_value' => null,
                'fee_currency' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => self::UNKNOWN_FEE_LABEL,
                // RIB postal is never invented — added from the admin UI.
                'fields' => [
                    ['label' => 'Titulaire du compte', 'value' => null, 'type' => 'text', 'is_public' => true, 'copyable' => true],
                    ['label' => 'RIB postal', 'value' => null, 'type' => 'text', 'is_public' => true, 'copyable' => true],
                ],
            ],

            // ── 8. USDT TRC20 ───────────────────────────────────────────
            [
                'key' => 'usdt_trc20',
                'label' => 'USDT TRC20',
                'category' => 'crypto',
                'currencies' => ['USD'],
                'is_public' => true,
                'requires_confirmation' => true,
                // Inactive until a wallet address is configured from the admin UI.
                'is_active' => false,
                'sort_order' => 80,
                'fee_type' => 'unknown',
                'fee_value' => null,
                'fee_currency' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => self::UNKNOWN_FEE_LABEL,
                'fields' => [
                    ['label' => 'Actif', 'value' => 'USDT', 'type' => 'text', 'is_public' => true, 'copyable' => false],
                    ['label' => 'Réseau', 'value' => 'TRC20', 'type' => 'text', 'is_public' => true, 'copyable' => false],
                    ['label' => 'Adresse wallet', 'value' => null, 'type' => 'wallet_address', 'is_public' => true, 'copyable' => true],
                ],
            ],

            // ── 9. USDT BEP20 ───────────────────────────────────────────
            [
                'key' => 'usdt_bep20',
                'label' => 'USDT BEP20',
                'category' => 'crypto',
                'currencies' => ['USD'],
                'is_public' => true,
                'requires_confirmation' => true,
                'is_active' => false,
                'sort_order' => 90,
                'fee_type' => 'unknown',
                'fee_value' => null,
                'fee_currency' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => self::UNKNOWN_FEE_LABEL,
                'fields' => [
                    ['label' => 'Actif', 'value' => 'USDT', 'type' => 'text', 'is_public' => true, 'copyable' => false],
                    ['label' => 'Réseau', 'value' => 'BEP20', 'type' => 'text', 'is_public' => true, 'copyable' => false],
                    ['label' => 'Adresse wallet', 'value' => null, 'type' => 'wallet_address', 'is_public' => true, 'copyable' => true],
                ],
            ],

            // ── Optional, created inactive ──────────────────────────────
            [
                'key' => 'carte_bancaire',
                'label' => 'Carte Bancaire',
                'category' => 'card',
                'currencies' => ['TND'],
                'is_public' => true,
                'requires_confirmation' => true,
                'is_active' => false,
                'sort_order' => 200,
                'fee_type' => 'unknown',
                'fee_value' => null,
                'fee_currency' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => self::UNKNOWN_FEE_LABEL,
                'fields' => [],
            ],
            [
                'key' => 'paymee',
                'label' => 'Paymee',
                'category' => 'gateway',
                'currencies' => ['TND'],
                'is_public' => true,
                'requires_confirmation' => true,
                'is_active' => false,
                'sort_order' => 210,
                'fee_type' => 'unknown',
                'fee_value' => null,
                'fee_currency' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => self::UNKNOWN_FEE_LABEL,
                'fields' => [],
            ],
            [
                'key' => 'konnect',
                'label' => 'Konnect',
                'category' => 'gateway',
                'currencies' => ['TND'],
                'is_public' => true,
                'requires_confirmation' => true,
                'is_active' => false,
                'sort_order' => 220,
                'fee_type' => 'unknown',
                'fee_value' => null,
                'fee_currency' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => self::UNKNOWN_FEE_LABEL,
                'fields' => [],
            ],
            [
                'key' => 'especes',
                'label' => 'Espèces',
                'category' => 'cash',
                'currencies' => ['TND'],
                'is_public' => true,
                'requires_confirmation' => false,
                'is_active' => false,
                'sort_order' => 230,
                'fee_type' => 'fixed',
                'fee_value' => 0,
                'fee_currency' => 'TND',
                'fee_paid_by' => 'customer',
                'fee_label' => null,
                'fields' => [],
            ],
        ];
    }
}
