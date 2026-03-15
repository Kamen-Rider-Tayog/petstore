<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Generate a URL for the application
 */
function url($path = '')
{
    return Config::baseUrl($path);
}

/**
 * Generate a URL for an asset
 */
function asset($path)
{
    return Config::asset($path);
}

/**
 * Redirect to a URL
 */
function redirect($path)
{
    header('Location: ' . url($path));
    exit;
}

/**
 * Get the base URL for JavaScript
 */
function getBaseUrlForJS()
{
    return Config::baseUrl();
}

/**
 * Escape HTML output
 */
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}