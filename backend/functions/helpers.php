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

/**
 * Render Untitled UI icon
 * @param string $name Icon name
 * @param int $size Icon size (16, 18, 20, 24)
 * @param string $class Additional CSS classes
 * @param bool $wrap Whether to wrap in icon container
 * @return string SVG HTML
 */
function icon($name, $size = 20, $class = '', $wrap = false)
{
    $icons = [
        // Navigation & Actions
        'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>',
        'cart' => '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.7 12.5a2 2 0 0 0 2 1.5h9.7a2 2 0 0 0 2-1.5l1.6-7.5H5.55"/>',
        'user' => '<circle cx="12" cy="8" r="5"/><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>',
        'menu' => '<path d="M3 12h18"/><path d="M3 6h18"/><path d="M3 18h18"/>',
        'close' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
        'arrow-left' => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
        'arrow-right' => '<path d="m12 5 7 7-7 7"/><path d="M5 12h14"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-up' => '<path d="m18 15-6-6-6 6"/>',
        'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
        'home' => '<path d="M3 9.5L12 3l9 6.5V20a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9.5z"/><path d="M9 22V12h6v10"/>',
        
        // Weather / Time
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>',
        'sunset' => '<path d="M12 10V2"/><path d="m4.93 10.93 1.41 1.41"/><path d="M2 18h2"/><path d="M20 18h2"/><path d="m19.07 10.93-1.41 1.41"/><path d="M22 22H2"/><path d="M16 6l-4 4-4-4"/><path d="M16 18a4 4 0 0 0-8 0"/>',
        'moon' => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>',
        
        // Contact & Communication
        'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 7L2 7"/>',
        'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'marker' => '<path d="M12 22c-2 0-8-5.5-8-10 0-4.5 3.5-8 8-8s8 3.5 8 8c0 4.5-6 10-8 10z"/><circle cx="12" cy="12" r="3"/>',
        'message' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        
        // Social Media
        'facebook' => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
        'instagram' => '<rect x="2" y="2" width="20" height="20" rx="4"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.5" fill="currentColor" stroke="none"/>',
        'tiktok' => '<path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/>',
        'youtube' => '<rect x="2" y="4" width="20" height="16" rx="3"/><path d="m10 10l5 3-5 3V10z"/>',
        'twitter' => '<path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.6 9 5-.5-2.2.5-4.6 2.6-5.9 2.1-1.3 4.9-1.1 6.8.6L22 4z"/>',
        
        // E-commerce
        'credit-card' => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
        'truck' => '<path d="M9 17a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/><path d="M19 17a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/><path d="M13 5h3.5l5 6v6h-8.5"/><path d="M1 11h13"/>',
        'package' => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="M3.3 7 12 12l8.7-5"/><path d="M12 22V12"/>',
        'tag' => '<path d="M12 2H2v10l9.29 9.29a2 2 0 0 0 2.83 0l7-7a2 2 0 0 0 0-2.83L12 2z"/><circle cx="7" cy="7" r="2"/>',
        'gift' => '<path d="M20 12v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8"/><path d="M4 8h16v4H4z"/><path d="M12 22v-8"/><path d="M12 8V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v0a2 2 0 0 0 2 2h4"/><path d="M12 8h4a2 2 0 0 0 2-2v0a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v2"/>',
        
        // Pets & Animals
        'paw' => '<circle cx="12" cy="12" r="2"/><circle cx="6" cy="8" r="2"/><circle cx="18" cy="8" r="2"/><circle cx="6" cy="16" r="2"/><circle cx="18" cy="16" r="2"/><path d="M12 20c-2.5 0-4-1.5-4-3s1.5-3 4-3 4 1.5 4 3-1.5 3-4 3z"/>',
        'dog' => '<circle cx="12" cy="8" r="2"/><path d="M5 12c0-3 2-5 7-5s7 2 7 5-2 5-7 5-7-2-7-5z"/><path d="M8 17v4"/><path d="M16 17v4"/>',
        'cat' => '<circle cx="12" cy="8" r="2"/><path d="M5 12c0-3 2-5 7-5s7 2 7 5-2 5-7 5-7-2-7-5z"/><path d="M8 17l-2 4"/><path d="M16 17l2 4"/>',
        'heart' => '<path d="M20 8.5c0-2.5-2-4.5-4.5-4.5-1.5 0-2.8.7-3.5 1.8-.7-1.1-2-1.8-3.5-1.8C6 4 4 6 4 8.5 4 13 12 20 12 20s8-7 8-11.5z"/>',
        'heart-filled' => '<path d="M20 8.5c0-2.5-2-4.5-4.5-4.5-1.5 0-2.8.7-3.5 1.8-.7-1.1-2-1.8-3.5-1.8C6 4 4 6 4 8.5 4 13 12 20 12 20s8-7 8-11.5z" fill="currentColor"/>',
        
        // Admin & Settings
        'settings' => '<path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/><path d="M19.4 15a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H5.78a1.65 1.65 0 0 0-1.51 1 1.65 1.65 0 0 0 .33 1.82L12 22z"/><path d="M4.6 9a1.65 1.65 0 0 0-.33 1.82c.23.5.85.9 1.51.9h12.44a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82L12 2z"/>',
        'dashboard' => '<rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>',
        'analytics' => '<path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M12 17V5"/><path d="M6 17v-3"/>',
        'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        
        // Files & Documents
        'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/><path d="M14 2v6h6"/>',
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
        'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>',
        'print' => '<path d="M6 9V3h12v6"/><path d="M6 21h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2z"/><path d="M18 9H6"/>',
        
        // Alerts & Status
        'check' => '<polyline points="20 6 9 17 4 12"/>',
        'x' => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
        'alert' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r="0.5" fill="currentColor"/>',
        'info' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><circle cx="12" cy="8" r="0.5" fill="currentColor"/>',
        'help' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        
        // Time & Calendar
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><circle cx="12" cy="15" r="0.5" fill="currentColor"/><circle cx="16" cy="15" r="0.5" fill="currentColor"/><circle cx="8" cy="15" r="0.5" fill="currentColor"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        
        // Ratings
        'star' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
        'star-filled' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="currentColor"/>',
        
        // Misc
        'eye' => '<circle cx="12" cy="12" r="3"/><path d="M22 12c-2.667 4.667-6 7-10 7s-7.333-2.333-10-7c2.667-4.667 6-7 10-7s7.333 2.333 10 7z"/>',
        'eye-off' => '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><line x1="1" y1="1" x2="23" y2="23"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>',
        'lock' => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'unlock' => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/>',
        'external-link' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>',
    
        'paw-outline' => '<circle cx="12" cy="12" r="2"/><circle cx="6" cy="8" r="2"/><circle cx="18" cy="8" r="2"/><circle cx="6" cy="16" r="2"/><circle cx="18" cy="16" r="2"/><path d="M12 20c-2.5 0-4-1.5-4-3s1.5-3 4-3 4 1.5 4 3-1.5 3-4 3z"/>',
        'calendar-outline' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'arrow-right-outline' => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',

        'back-to-top' => '<path d="M16 12L12 8M12 8L8 12M12 8V16M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z"/>',
    ];

    $class = $class ? ' class="' . $class . '"' : '';
    $paths = $icons[$name] ?? '<circle cx="12" cy="12" r="10"/>';
    
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"' . $class . '>' . $paths . '</svg>';
    
    if ($wrap) {
        return '<span class="icon-wrapper">' . $svg . '</span>';
    }
    
    return $svg;
}

/**
 * Render star rating
 * @param int $rating Rating value (1-5)
 * @param int $size Icon size
 * @return string
 */
function star_rating($rating, $size = 16)
{
    $output = '<div class="star-rating">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $output .= icon('star-filled', $size, 'star-filled');
        } else {
            $output .= icon('star', $size, 'star');
        }
    }
    $output .= '</div>';
    return $output;
}

/**
 * Convert timestamp to "time ago" string
 */
function time_ago($timestamp) {
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return $diff . ' seconds ago';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $timestamp);
    }
}