<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

/**
 * Idempotent: safe to run any number of times. Each method is upserted by
 * its unique `key`, so re-running never creates duplicates — it just
 * converges every listed method back to this canonical configuration.
 *
 * Rule for unknown fees: never store an unknown fee as 0. Unknown fees use
 * fee_type=unknown, fee_value=null, fee_paid_by=customer, and a fee_label
 * explaining the fee is charged to the customer if any applies.
 *
 * Rule for account/wallet details: never invent a RIB or wallet address.
 * Those fields are seeded null and must be filled in from the admin UI
 * (Payments > Payment Methods > Edit details).
 */
class PaymentMethodSeeder extends Seeder
{
    private const UNKNOWN_FEE_LABEL = 'Les éventuels frais de paiement sont à la charge du client.';

    public function run(): void
    {
        foreach ($this->methods() as $method) {
            PaymentMethod::updateOrCreate(
                ['key' => $method['key']],
                $method
            );
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
                'requires_confirmation' => true,
                'is_active' => true,
                'sort_order' => 10,
                'fee_type' => 'percentage',
                'fee_value' => 1,
                'fee_paid_by' => 'customer',
                'fee_label' => null,
                'details' => [
                    'contacts' => [
                        ['name' => null, 'phone' => '92 871 752'],
                        ['name' => null, 'phone' => '25 208 023'],
                    ],
                ],
            ],

            // ── 2. Flouci ───────────────────────────────────────────────
            [
                'key' => 'flouci',
                'label' => 'Flouci',
                'category' => 'wallet',
                'currencies' => ['TND'],
                'requires_confirmation' => true,
                'is_active' => true,
                'sort_order' => 20,
                'fee_type' => 'unknown',
                'fee_value' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => self::UNKNOWN_FEE_LABEL,
                'details' => [
                    'contacts' => [
                        ['name' => 'Khaled Chtioui', 'phone' => '92 871 752'],
                        ['name' => 'Dhia Boubaker', 'phone' => '25 208 023'],
                    ],
                ],
            ],

            // ── 3. WafaCash ─────────────────────────────────────────────
            [
                'key' => 'wafacash',
                'label' => 'WafaCash',
                'category' => 'agency',
                'currencies' => ['TND'],
                'requires_confirmation' => true,
                'is_active' => true,
                'sort_order' => 30,
                'fee_type' => 'unknown',
                'fee_value' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => self::UNKNOWN_FEE_LABEL,
                'details' => [
                    'contacts' => [
                        ['name' => 'Khaled Chtioui', 'phone' => '92 871 752'],
                    ],
                    'agency' => 'Agence Jemmel',
                ],
            ],

            // ── 4. IZI Zitouna ──────────────────────────────────────────
            [
                'key' => 'izi_zitouna',
                'label' => 'IZI Zitouna',
                'category' => 'agency',
                'currencies' => ['TND'],
                'requires_confirmation' => true,
                'is_active' => true,
                'sort_order' => 40,
                'fee_type' => 'unknown',
                'fee_value' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => self::UNKNOWN_FEE_LABEL,
                'details' => [
                    'contacts' => [
                        ['name' => 'Khaled Chtioui', 'phone' => '92 871 752'],
                    ],
                    'agency' => 'Agence Jemmel',
                ],
            ],

            // ── 5. Kashy ────────────────────────────────────────────────
            [
                'key' => 'kashy',
                'label' => 'Kashy',
                'category' => 'wallet',
                'currencies' => ['TND'],
                'requires_confirmation' => true,
                'is_active' => true,
                'sort_order' => 50,
                'fee_type' => 'unknown',
                'fee_value' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => self::UNKNOWN_FEE_LABEL,
                'details' => [
                    'contacts' => [
                        ['name' => 'Khaled Chtioui', 'phone' => '92 871 752'],
                    ],
                ],
            ],

            // ── 6. Virement bancaire / RIB ──────────────────────────────
            [
                'key' => 'virement_bancaire',
                'label' => 'Virement Bancaire',
                'category' => 'bank_transfer',
                'currencies' => ['TND', 'EUR', 'USD'],
                'requires_confirmation' => true,
                'is_active' => true,
                'sort_order' => 60,
                'fee_type' => 'fixed',
                'fee_value' => 2,
                'fee_paid_by' => 'customer',
                'fee_label' => null,
                // Bank account(s) are never invented — added from the admin UI.
                'details' => ['holder' => null, 'rib' => null, 'bank_name' => null],
            ],

            // ── 7. Virement postal / RIB postal ─────────────────────────
            [
                'key' => 'virement_postal',
                'label' => 'Virement Postal',
                'category' => 'postal_transfer',
                'currencies' => ['TND'],
                'requires_confirmation' => true,
                'is_active' => true,
                'sort_order' => 70,
                'fee_type' => 'unknown',
                'fee_value' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => self::UNKNOWN_FEE_LABEL,
                // RIB postal is never invented — added from the admin UI.
                'details' => ['holder' => null, 'rib_postal' => null],
            ],

            // ── 8. USDT TRC20 ───────────────────────────────────────────
            [
                'key' => 'usdt_trc20',
                'label' => 'USDT TRC20',
                'category' => 'crypto',
                'currencies' => ['USD'],
                'requires_confirmation' => true,
                // Inactive until a wallet address is configured from the admin UI.
                'is_active' => false,
                'sort_order' => 80,
                'fee_type' => 'unknown',
                'fee_value' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => self::UNKNOWN_FEE_LABEL,
                'details' => ['asset' => 'USDT', 'network' => 'TRC20', 'wallet_address' => null],
            ],

            // ── 9. USDT BEP20 ───────────────────────────────────────────
            [
                'key' => 'usdt_bep20',
                'label' => 'USDT BEP20',
                'category' => 'crypto',
                'currencies' => ['USD'],
                'requires_confirmation' => true,
                'is_active' => false,
                'sort_order' => 90,
                'fee_type' => 'unknown',
                'fee_value' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => self::UNKNOWN_FEE_LABEL,
                'details' => ['asset' => 'USDT', 'network' => 'BEP20', 'wallet_address' => null],
            ],

            // ── Optional, created inactive ──────────────────────────────
            [
                'key' => 'carte_bancaire',
                'label' => 'Carte Bancaire',
                'category' => 'card',
                'currencies' => ['TND'],
                'requires_confirmation' => true,
                'is_active' => false,
                'sort_order' => 200,
                'fee_type' => 'unknown',
                'fee_value' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => self::UNKNOWN_FEE_LABEL,
                'details' => null,
            ],
            [
                'key' => 'paymee',
                'label' => 'Paymee',
                'category' => 'gateway',
                'currencies' => ['TND'],
                'requires_confirmation' => true,
                'is_active' => false,
                'sort_order' => 210,
                'fee_type' => 'unknown',
                'fee_value' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => self::UNKNOWN_FEE_LABEL,
                'details' => null,
            ],
            [
                'key' => 'konnect',
                'label' => 'Konnect',
                'category' => 'gateway',
                'currencies' => ['TND'],
                'requires_confirmation' => true,
                'is_active' => false,
                'sort_order' => 220,
                'fee_type' => 'unknown',
                'fee_value' => null,
                'fee_paid_by' => 'customer',
                'fee_label' => self::UNKNOWN_FEE_LABEL,
                'details' => null,
            ],
            [
                'key' => 'especes',
                'label' => 'Espèces',
                'category' => 'cash',
                'currencies' => ['TND'],
                'requires_confirmation' => false,
                'is_active' => false,
                'sort_order' => 230,
                'fee_type' => 'fixed',
                'fee_value' => 0,
                'fee_paid_by' => 'customer',
                'fee_label' => null,
                'details' => null,
            ],
        ];
    }
}
