<?php
/**
 * Simple Cache Helper
 * File-based caching (upgrade to Redis/Memcached for production)
 */

class SimpleCache {
    private static $cacheDir = __DIR__ . '/../cache/';
    
    /**
     * Initialize cache directory
     */
    public static function init() {
        if (!file_exists(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0777, true);
        }
    }
    
    /**
     * Get cached data
     */
    public static function get($key) {
        self::init();
        $file = self::$cacheDir . md5($key) . '.cache';
        
        if (!file_exists($file)) {
            return null;
        }
        
        $data = json_decode(file_get_contents($file), true);
        
        // Check if expired
        if ($data['expires'] < time()) {
            unlink($file);
            return null;
        }
        
        return $data['value'];
    }
    
    /**
     * Set cache data
     */
    public static function set($key, $value, $ttl = 300) {
        self::init();
        $file = self::$cacheDir . md5($key) . '.cache';
        
        $data = [
            'key' => $key,
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        file_put_contents($file, json_encode($data));
        return true;
    }
    
    /**
     * Delete cached data
     */
    public static function delete($key) {
        self::init();
        $file = self::$cacheDir . md5($key) . '.cache';
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return true;
    }
    
    /**
     * Clear all cache
     */
    public static function clear() {
        self::init();
        $files = glob(self::$cacheDir . '*.cache');
        
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }
    
    /**
     * Cache with tags for group invalidation
     */
    public static function setWithTags($key, $value, $tags = [], $ttl = 300) {
        // Set the main cache
        self::set($key, $value, $ttl);
        
        // Store tags
        foreach ($tags as $tag) {
            $tagFile = self::$cacheDir . 'tag_' . md5($tag) . '.json';
            
            if (file_exists($tagFile)) {
                $tagData = json_decode(file_get_contents($tagFile), true);
            } else {
                $tagData = [];
            }
            
            $tagData[$key] = time() + $ttl;
            file_put_contents($tagFile, json_encode($tagData));
        }
    }
    
    /**
     * Clear cache by tag
     */
    public static function clearByTag($tag) {
        self::init();
        $tagFile = self::$cacheDir . 'tag_' . md5($tag) . '.json';
        
        if (file_exists($tagFile)) {
            $tagData = json_decode(file_get_contents($tagFile), true);
            
            foreach ($tagData as $key => $expires) {
                self::delete($key);
            }
            
            unlink($tagFile);
        }
    }
}

/**
 * Cache helper functions
 */
function getCached($key, $callback, $ttl = 300) {
    $cached = SimpleCache::get($key);
    
    if ($cached !== null) {
        return $cached;
    }
    
    $value = $callback();
    SimpleCache::set($key, $value, $ttl);
    
    return $value;
}

/**
 * Cache products with automatic invalidation
 */
function cacheProducts($key, $data, $ttl = 300) {
    SimpleCache::setWithTags($key, $data, ['products'], $ttl);
}

/**
 * Clear product cache
 */
function clearProductCache() {
    SimpleCache::clearByTag('products');
}
