<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default temporary password for newly-created client accounts.
    |--------------------------------------------------------------------------
    */
    'default_client_password' => env('FINIX_DEFAULT_CLIENT_PASSWORD', 'Finix@Tn'),

    /*
    |--------------------------------------------------------------------------
    | Domain used to auto-generate a client's Finix system email address.
    |--------------------------------------------------------------------------
    | This address is a client-facing identifier only. It is NOT used to send
    | real emails unless this domain is actually configured to send/receive
    | mail — see FinixEmailGeneratorService.
    */
    'email_domain' => env('FINIX_EMAIL_DOMAIN', 'finix.tn'),
];
