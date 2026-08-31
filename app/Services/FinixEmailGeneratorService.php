<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Str;

/**
 * Generates the client-facing Finix system email address (e.g. achiheb@finix.tn)
 * from a client's full name: first letter of the first name + last name,
 * lowercase, accents and special characters stripped.
 *
 * This is an internal identifier only — it is NOT wired to actually send or
 * receive mail unless the configured domain is genuinely set up for that.
 */
class FinixEmailGeneratorService
{
    public function generate(string $fullName): string
    {
        $local = $this->localPart($fullName);
        $domain = config('finix.email_domain', 'finix.tn');

        $candidate = "{$local}@{$domain}";
        $suffix = 2;

        while (Client::where('finix_email', $candidate)->exists()) {
            $candidate = "{$local}{$suffix}@{$domain}";
            $suffix++;
        }

        return $candidate;
    }

    private function localPart(string $fullName): string
    {
        $clean = Str::of($fullName)->ascii()->trim();
        $parts = preg_split('/\s+/', (string) $clean, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($parts)) {
            return 'client';
        }

        if (count($parts) === 1) {
            $local = $parts[0];
        } else {
            $firstName = $parts[0];
            $lastName = end($parts);
            $local = mb_substr($firstName, 0, 1) . $lastName;
        }

        $local = Str::lower($local);
        $local = preg_replace('/[^a-z0-9]/', '', $local);

        return $local !== '' ? $local : 'client';
    }
}
