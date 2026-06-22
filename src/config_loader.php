<?php

$configFile = __DIR__ . '/config.php';

if (is_file($configFile)) {
    return require $configFile;
}

return [
    'smtp' => [
        'host'   => getenv('SMTP_HOST')   ?: 'smtp-relay.brevo.com',
        'port'   => (int)(getenv('SMTP_PORT') ?: 587),
        'secure' => getenv('SMTP_SECURE') ?: 'tls',
        'user'   => getenv('SMTP_USER')   ?: '',
        'pass'   => getenv('SMTP_PASS')   ?: '',
    ],
    'mail' => [
        'from_name'    => getenv('MAIL_FROM_NAME')    ?: 'Odevzdávání prací',
        'from_address' => getenv('MAIL_FROM_ADDRESS') ?: '',
    ],
    'drive' => [
        'folder_id'        => getenv('DRIVE_FOLDER_ID')          ?: '',
        'credentials_json' => getenv('GOOGLE_CREDENTIALS_JSON')  ?: '',
    ],
    'limits' => [
        'max_file_mb'    => 10,
        'max_files'      => 20,
        'allowed_images' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
    ],
];
