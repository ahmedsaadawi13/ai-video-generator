// FILE: /config/app.php
<?php

/**
 * Application Configuration
 */

return [
    'name' => 'AI Video Generator',
    'version' => '1.0.0',
    'timezone' => 'UTC',
    'environment' => getenv('APP_ENV') ?: 'production',
    'debug' => getenv('APP_DEBUG') === 'true',

    // File upload settings
    'upload' => [
        'max_size' => 100 * 1024 * 1024, // 100MB
        'allowed_image_types' => ['jpg', 'jpeg', 'png'],
        'allowed_video_types' => ['mp4', 'mov'],
        'allowed_audio_types' => ['mp3', 'wav'],
    ],

    // Pagination
    'pagination' => [
        'per_page' => 20,
    ],

    // Session
    'session' => [
        'lifetime' => 7200, // 2 hours
        'name' => 'ai_video_session',
    ],
];
