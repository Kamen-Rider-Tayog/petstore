<?php
/**
 * Simple File-Based Caching System
 * Stores cached data for 1 hour by default
 */
class Cache
{
    private static $cache_dir = __DIR__ . '/../../storage/cache/';
    private static $default_ttl = 3600; // 1 hour

    /**
     * Initialize cache directory
     */
    public static function init()
    {
        if (!file_exists(self::$cache_dir)) {
            mkdir(self::$cache_dir, 0755, true);
        }
    }

    /**
     * Get cached value
     */
    public static function get($key, $default = null)
    {
        self::init();
        $file = self::$cache_dir . md5($key) . '.cache';

        if (!file_exists($file)) {
            return $default;
        }

        $data = unserialize(file_get_contents($file));

        // Check if expired
        if ($data['expires'] < time()) {
            unlink($file);
            return $default;
        }

        return $data['value'];
    }

    /**
     * Set cached value
     */
    public static function put($key, $value, $ttl = null)
    {
        self::init();
        $file = self::$cache_dir . md5($key) . '.cache';
        $ttl = $ttl ?? self::$default_ttl;

        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
        ];

        file_put_contents($file, serialize($data));
        return true;
    }

    /**
     * Check if key exists and not expired
     */
    public static function has($key)
    {
        self::init();
        $file = self::$cache_dir . md5($key) . '.cache';

        if (!file_exists($file)) {
            return false;
        }

        $data = unserialize(file_get_contents($file));
        if ($data['expires'] < time()) {
            unlink($file);
            return false;
        }

        return true;
    }

    /**
     * Forget cached value
     */
    public static function forget($key)
    {
        self::init();
        $file = self::$cache_dir . md5($key) . '.cache';

        if (file_exists($file)) {
            unlink($file);
        }

        return true;
    }

    /**
     * Flush all cache
     */
    public static function flush()
    {
        self::init();
        $files = glob(self::$cache_dir . '*.cache');

        foreach ($files as $file) {
            unlink($file);
        }

        return true;
    }
}

// Initialize cache
Cache::init();
?>
