<?php

return [
    'smtp' => [
        'host'   => 'smtp-relay.brevo.com',
        'port'   => 587,
        'secure' => 'tls',
        'user'   => 'tvuj-brevo-login@smtp-brevo.com',
        'pass'   => 'tvuj-brevo-smtp-key',
    ],

    'mail' => [
        'from_name'    => 'Odevzdávání prací',
        'from_address' => 'overeny-odesilatel@domena.cz', // example configu -> vlož sem email z smtp provideru který je ověřený
    ],

    'limits' => [
        'max_file_mb'      => 10,
        'max_files'        => 20,
        'allowed_images'   => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
    ],
];
