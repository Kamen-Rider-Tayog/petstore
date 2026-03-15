<?php
/**
 * Configuration Loader
 * Loads environment variables from .env file
 */

class Config
{
    private static $config = [];
    
    /**
     * Load .env file
     */
    public static function load()
    {
        $env_file = __DIR__ . '/../../.env';
        
        if (file_exists($env_file)) {
            $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                // Skip comments
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                // Parse KEY=VALUE pairs
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remove quotes if present
                    if (strpos($value, '"') === 0 || strpos($value, "'") === 0) {
                        $value = substr($value, 1, -1);
                    }
                    
                    self::$config[$key] = $value;
                    
                    // Set as environment variable
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        } else {
            // Fallback to default values if .env doesn't exist
            self::loadDefaults();
        }
    }
    
    /**
     * Load default configuration
     */
    private static function loadDefaults()
    {
        self::$config = [
            'APP_NAME' => 'Pet Store',
            'APP_ENV' => 'development',
            'APP_URL' => 'http://localhost/petstore',
            'APP_DEBUG' => true,
            'DB_HOST' => 'localhost',
            'DB_USER' => 'root',
            'DB_PASS' => '',
            'DB_NAME' => 'pet_store',
            'UPLOAD_MAX_SIZE' => 2097152,
            'UPLOAD_ALLOWED_TYPES' => 'image/jpeg,image/png,image/gif',
            'UPLOAD_PATH' => '../assets/uploads/'
        ];
        
        foreach (self::$config as $key => $value) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
    
    /**
     * Get configuration value
     */
    public static function get($key, $default = null)
    {
        if (isset(self::$config[$key])) {
            return self::$config[$key];
        }
        
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        return $default;
    }
    
    /**
     * Get base URL
     */
    public static function baseUrl($path = '')
    {
        $url = self::get('APP_URL', 'http://localhost/petstore');
        return $url . ($path ? '/' . ltrim($path, '/') : '');
    }
    
    /**
     * Get asset URL
     */
    public static function asset($path)
    {
        return self::baseUrl('assets/' . ltrim($path, '/'));
    }
    
    /**
     * Check if in development mode
     */
    public static function isDevelopment()
    {
        return self::get('APP_ENV') === 'development';
    }
    
    /**
     * Check if debug mode is enabled
     */
    public static function isDebug()
    {
        return self::get('APP_DEBUG') === true || self::get('APP_DEBUG') === 'true';
    }
}

// Load configuration
Config::load();

// Define constants for easy access
define('BASE_URL', Config::baseUrl());
define('APP_NAME', Config::get('APP_NAME'));
define('UPLOAD_PATH', Config::get('UPLOAD_PATH'));
define('MAX_FILE_SIZE', Config::get('UPLOAD_MAX_SIZE'));
?>