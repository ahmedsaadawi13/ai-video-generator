// FILE: /app/helpers/functions.php
<?php

/**
 * Helper Functions
 *
 * Global utility functions available throughout the application
 */

/**
 * Redirect to URL
 */
function redirect($url)
{
    header("Location: $url");
    exit;
}

/**
 * Escape HTML output
 */
function e($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Set flash message
 */
function setFlash($key, $message)
{
    $_SESSION['flash'][$key] = $message;
}

/**
 * Get flash message
 */
function getFlash($key)
{
    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}

/**
 * Format bytes to human readable
 */
function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Format duration in seconds to human readable
 */
function formatDuration($seconds)
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;

    if ($hours > 0) {
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
    return sprintf('%02d:%02d', $minutes, $secs);
}

/**
 * Sanitize filename
 */
function sanitizeFilename($filename)
{
    // Remove any path components
    $filename = basename($filename);

    // Replace spaces with underscores
    $filename = str_replace(' ', '_', $filename);

    // Remove any character that is not alphanumeric, underscore, hyphen, or dot
    $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $filename);

    return $filename;
}

/**
 * Generate random string
 */
function generateRandomString($length = 32)
{
    return bin2hex(random_bytes($length / 2));
}

/**
 * Generate unique filename
 */
function generateUniqueFilename($originalFilename)
{
    $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
    $basename = pathinfo($originalFilename, PATHINFO_FILENAME);
    $basename = sanitizeFilename($basename);

    return $basename . '_' . time() . '_' . generateRandomString(8) . '.' . $extension;
}

/**
 * Validate file type
 */
function validateFileType($filename, $allowedTypes)
{
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $allowedTypes);
}

/**
 * Get file extension
 */
function getFileExtension($filename)
{
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if user is authenticated
 */
function isAuthenticated()
{
    return isset($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function currentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current tenant ID
 */
function currentTenantId()
{
    return $_SESSION['tenant_id'] ?? null;
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'Y-m-d H:i:s')
{
    if (empty($date)) {
        return '';
    }

    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Get relative time (e.g., "2 hours ago")
 */
function timeAgo($datetime)
{
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;

    if ($difference < 60) {
        return 'just now';
    } elseif ($difference < 3600) {
        $minutes = floor($difference / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 604800) {
        $days = floor($difference / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $timestamp);
    }
}

/**
 * Paginate array
 */
function paginate($array, $page, $perPage)
{
    $offset = ($page - 1) * $perPage;
    return array_slice($array, $offset, $perPage);
}

/**
 * Calculate total pages
 */
function totalPages($total, $perPage)
{
    return ceil($total / $perPage);
}

/**
 * Get URL with query parameters
 */
function urlWithParams($path, $params = [])
{
    if (empty($params)) {
        return $path;
    }

    $query = http_build_query($params);
    return $path . '?' . $query;
}

/**
 * Check if quota is exceeded
 */
function isQuotaExceeded($current, $limit)
{
    if ($limit === null || $limit < 0) {
        return false; // Unlimited
    }

    return $current >= $limit;
}

/**
 * Calculate percentage
 */
function calculatePercentage($current, $total)
{
    if ($total == 0) {
        return 0;
    }

    return round(($current / $total) * 100, 2);
}
