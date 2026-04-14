<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    private $prefix = 'vehicle_api';
    private $defaultTtl = 3600; // 1 hour

    /**
     * Remember value in cache
     */
    public function remember(string $key, $ttl, callable $callback)
    {
        $cacheKey = $this->getCacheKey($key);
        $ttl = $ttl ?? $this->defaultTtl;

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Get from cache
     */
    public function get(string $key)
    {
        return Cache::get($this->getCacheKey($key));
    }

    /**
     * Put in cache
     */
    public function put(string $key, $value, $ttl = null)
    {
        $ttl = $ttl ?? $this->defaultTtl;
        return Cache::put($this->getCacheKey($key), $value, $ttl);
    }

    /**
     * Forget from cache
     */
    public function forget(string $key)
    {
        return Cache::forget($this->getCacheKey($key));
    }

    /**
     * Flush pattern
     */
    public function forgetPattern(string $pattern)
    {
        $keys = Cache::getRedis()->keys($this->prefix . ':' . $pattern);
        foreach ($keys as $key) {
            Cache::forget(str_replace($this->prefix . ':', '', $key));
        }
    }

    /**
     * Get cache key with prefix
     */
    private function getCacheKey(string $key): string
    {
        return $this->prefix . ':' . $key;
    }
}
