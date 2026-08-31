<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

/**
 * Idempotent: safe to run any number of times. Each method is upserted by
 * its unique `key`, so re-running never creates duplicates.
 *
 * Two categories of field are treated very differently on re-run:
 *
 *  - Baseline fields (label, category, currencies, is_public,
 *    requires_confirmation, sort_order) converge back to this canonical
 *    definition every time — these aren't things an admin customizes.
 *
 *  - Fee configuration (fee_type, fee_value, fee_currency, fee_paid_by,
 *    fee_label) and contact/account fields are admin-customizable through
 *    the UI, so they are only ever set while the method's fee config is
 *    still in its untouched, never-configured state (fee_value,
 *    fee_currency and fee_label all null — the shape left by
 *    create_payment_methods_table's raw baseline insert, before either
 *    this seeder or an admin has ever set real fee data). Once any of
 *    those three fields has a real value — whether set by this seeder on
 *    a prior run or by an admin through the UI — they are never touched
 *    again, no matter what this seeder's own definition says. An admin's
 *    fee setup must never be silently reset back to a seeder default.
 *    (This was previously a real bug: updateOrCreate() unconditionally
 *    overwrote fee_* on every run, wiping real fee configuration on live
 *    data — see PaymentMethodSeederTest::test_reseeding_never_changes_an_already_customized_fee_configuration.)
 *
 * Rule for unknown fees (at first creation only): never store an unknown
 * fee as 0. Unknown fees use fee_type=unknown, fee_value=null,
 * fee_paid_by=customer, and a fee_label explaining the fee is charged to
 * the customer if any applies.
 *
 * Rule for account/wallet details: never invent a RIB or wallet address.
 * Seeded values are only ever the real, known contact info from the
 * business — everything else (RIB, wallet address...) starts null and is
 * only ever filled in from the admin UI.
 */
class PaymentMethodSeeder extends Seeder
{
    private const UNKNOWN_FEE_LABEL = PaymentMethod::UNKNOWN_FEE_LABEL;

    private const FEE_KEYS = ['fee_type', 'fee_value', 'fee_currency', 'fee_paid_by', 'fee_label'];

    public function run(): void
    {
        foreach ($this->methods() as $definition) {
            $fields = $definition['fields'] ?? [];
            unset($definition['fields']);

            $feeDefaults = array_intersect_key($definition, array_flip(self::FEE_KEYS));
            $baselineDefinition = array_diff_key($definition, array_flip(self::FEE_KEYS));

            $existing = PaymentMethod::where('key', $definition['key'])->first();

            if ($existing) {
                $neverConfigured = is_null($existing->fee_value)
                    && is_null($existing->fee_currency)
                    && is_null($existing->fee_label);

                // Only fill in fee config the first time it's ever set —
                // e.g. a bare row left by the table's baseline migration
                // insert. Once fee_value/fee_currency/fee_label hold any
                // real value (seeded before, or set by an admin), leave
                // them exactly as they are.
                $updateData = $neverConfigured
                    ? array_merge($baselineDefinition, $feeDefaults)
                    : $baselineDefinition;

                $existing->fill($updateData);
                $existing->save();
                $method = $existing;
            } else {
                // Brand new method: seed the full definition, fees included.
                $method = PaymentMethod::create(array_merge($baselineDefinition, $feeDefaults, [
                    'key' => $definition['key'],
                ]));
            }

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
