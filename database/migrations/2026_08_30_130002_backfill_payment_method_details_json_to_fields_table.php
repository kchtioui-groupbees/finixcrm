<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * One-time data migration: the old ad-hoc `payment_methods.details` JSON
 * blob (contacts/agency/rib/wallet_address...) is copied into the new,
 * generic `payment_method_fields` table so nothing already personalized by
 * an admin or the seeder is lost. The `details` column itself is left in
 * place (untouched, unused going forward) rather than dropped — additive
 * only, nothing destructive.
 *
 * A method that already has fields (e.g. re-running after a partial
 * deploy) is left untouched — this only ever fills in an empty slate.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $methods = DB::table('payment_methods')->whereNotNull('details')->get();

        foreach ($methods as $method) {
            $alreadyHasFields = DB::table('payment_method_fields')
                ->where('payment_method_id', $method->id)
                ->exists();
            if ($alreadyHasFields) {
                continue;
            }

            $details = json_decode($method->details, true);
            if (!is_array($details) || empty($details)) {
                continue;
            }

            $fields = [];

            $contacts = $details['contacts'] ?? [];
            $multipleNames = count(array_filter($contacts, fn ($c) => !empty($c['name']))) > 1;
            $multiplePhones = count(array_filter($contacts, fn ($c) => !empty($c['phone']))) > 1;
            $nameIndex = 0;
            $phoneIndex = 0;

            foreach ($contacts as $contact) {
                if (!empty($contact['name'])) {
                    $nameIndex++;
                    $label = $multipleNames ? "Titulaire {$nameIndex}" : 'Titulaire';
                    $fields[] = ['label' => $label, 'value' => $contact['name'], 'type' => 'text', 'copyable' => false];
                }
                if (!empty($contact['phone'])) {
                    $phoneIndex++;
                    $label = $multiplePhones ? "Numéro de téléphone {$phoneIndex}" : 'Numéro de téléphone';
                    $fields[] = ['label' => $label, 'value' => $contact['phone'], 'type' => 'phone', 'copyable' => true];
                }
            }

            if (!empty($details['agency'])) {
                $fields[] = ['label' => 'Agence', 'value' => $details['agency'], 'type' => 'text', 'copyable' => false];
            }
            if (!empty($details['holder'] ?? null)) {
                $fields[] = ['label' => 'Titulaire du compte', 'value' => $details['holder'], 'type' => 'text', 'copyable' => true];
            }
            if (!empty($details['bank_name'] ?? null)) {
                $fields[] = ['label' => 'Nom de la banque', 'value' => $details['bank_name'], 'type' => 'text', 'copyable' => false];
            }
            if (!empty($details['rib'] ?? null)) {
                $fields[] = ['label' => 'RIB', 'value' => $details['rib'], 'type' => 'text', 'copyable' => true];
            }
            if (!empty($details['rib_postal'] ?? null)) {
                $fields[] = ['label' => 'RIB postal', 'value' => $details['rib_postal'], 'type' => 'text', 'copyable' => true];
            }
            if (!empty($details['wallet_address'] ?? null)) {
                $fields[] = ['label' => 'Adresse wallet', 'value' => $details['wallet_address'], 'type' => 'wallet_address', 'copyable' => true];
            }

            foreach ($fields as $order => $field) {
                DB::table('payment_method_fields')->insert([
                    'payment_method_id' => $method->id,
                    'label' => $field['label'],
                    'value' => $field['value'],
                    'type' => $field['type'],
                    'is_public' => true,
                    'copyable' => $field['copyable'],
                    'sort_order' => $order,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Data-only migration — nothing structural to reverse. Rolling back
        // does not delete the migrated field rows, since an admin may have
        // since edited them.
    }
};
