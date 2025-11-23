// FILE: /config/database.php
<?php

/**
 * Database Configuration
 *
 * Load database settings from environment variables or use defaults
 */

return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'database' => getenv('DB_DATABASE') ?: 'ai_video_generator',
    'username' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
    'charset' => 'utf8mb4',
];
