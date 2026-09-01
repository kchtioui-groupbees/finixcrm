<?php

namespace App\Services;

use App\Models\Client;
use App\Models\User;
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

        // Both tables have to be clear: the address becomes the client's
        // finix_email AND the email of the portal login created for them, so
        // checking only one of the two unique indexes still ends in a 1062.
        while ($this->taken($candidate)) {
            $candidate = "{$local}{$suffix}@{$domain}";
            $suffix++;
        }

        return $candidate;
    }

    private function taken(string $candidate): bool
    {
        return Client::where('finix_email', $candidate)->exists()
            || User::where('email', $candidate)->exists();
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
