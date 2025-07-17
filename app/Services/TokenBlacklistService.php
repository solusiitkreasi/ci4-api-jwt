<?php

namespace App\Services;

class TokenBlacklistService
{
    protected $cache;
    
    public function __construct()
    {
        $this->cache = \Config\Services::cache();
    }
    
    /**
     * Add token to blacklist
     */
    public function blacklistToken(string $token, int $expirationTime): bool
    {
        $tokenHash = hash('sha256', $token);
        $key = "blacklisted_token:{$tokenHash}";
        
        // Store until token naturally expires
        $ttl = $expirationTime - time();
        
        if ($ttl > 0) {
            return $this->cache->save($key, true, $ttl);
        }
        
        return true; // Already expired
    }
    
    /**
     * Check if token is blacklisted
     */
    public function isBlacklisted(string $token): bool
    {
        $tokenHash = hash('sha256', $token);
        $key = "blacklisted_token:{$tokenHash}";
        
        return $this->cache->get($key) !== null;
    }
    
    /**
     * Clean up expired blacklist entries (called by cron)
     */
    public function cleanup(): int
    {
        // This would depend on your cache implementation
        // For Redis, you could use SCAN to find and delete expired keys
        // For file cache, CodeIgniter handles this automatically
        return 0;
    }
}
