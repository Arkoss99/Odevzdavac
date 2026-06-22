<?php

$configFile = __DIR__ . '/config.php';

if (is_file($configFile)) {
    return require $configFile;
}

return [
    'brevo_api_key' => getenv('BREVO_API_KEY') ?: '',
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
