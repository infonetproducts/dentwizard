# Render Deployment & Scaling Guide

## Architecture Overview

```
[Users] → [Render (React App)] → [PHP API Server] → [MySQL Database]
               ↓                        ↓
         [Auto-scales]            [Fixed capacity]
```

## Render Setup for React App

### 1. Environment Variables (Set in Render Dashboard)
```bash
REACT_APP_API_URL=https://api.yourdomain.com/v1
REACT_APP_SSO_AUTHORITY=https://your-sso-provider.com
REACT_APP_SSO_CLIENT_ID=your-client-id
REACT_APP_SSO_REDIRECT_URI=https://your-app.onrender.com/callback
NODE_ENV=production
```

### 2. Build Settings
```yaml
Build Command: npm run build
Start Command: serve -s build
```

### 3. Headers Configuration (_headers file in public folder)
```
/*
  X-Frame-Options: DENY
  X-Content-Type-Options: nosniff
  X-XSS-Protection: 1; mode=block
  Referrer-Policy: strict-origin-when-cross-origin
  Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; connect-src 'self' https://api.yourdomain.com
```

## PHP API Optimizations for Render Traffic

### 1. Connection Pooling
```php
// config/database.php
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => true,  // Persistent connections
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->pdo;
    }
}

// Use: $pdo = Database::getInstance();
```

### 2. API Rate Limiting by Tier
```php
// helpers/rate_limit_tiers.php
function getRateLimitTier($request) {
    // Different limits for different operations
    $tiers = [
        'public' => ['requests' => 100, 'window' => 3600],
        'authenticated' => ['requests' => 1000, 'window' => 3600],
        'premium' => ['requests' => 5000, 'window' => 3600],
        'sensitive' => ['requests' => 10, 'window' => 3600]
    ];
    
    $endpoint = $_SERVER['REQUEST_URI'];
    
    // Sensitive endpoints (gift cards, payments)
    if (strpos($endpoint, 'giftcard') || strpos($endpoint, 'payment')) {
        return $tiers['sensitive'];
    }
    
    // Check authentication
    if (isset($_SESSION['user_id'])) {
        return $tiers['authenticated'];
    }
    
    return $tiers['public'];
}
```

### 3. Caching Strategy for High Traffic
```php
// Product listing with caching
function getProducts($params) {
    $cacheKey = 'products:' . md5(serialize($params));
    
    // Try cache first
    $cached = SimpleCache::get($cacheKey);
    if ($cached !== null) {
        header('X-Cache: HIT');
        return $cached;
    }
    
    // Query database
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare("
        SELECT * FROM Items 
        WHERE status = 'active' 
        LIMIT :limit OFFSET :offset
    ");
    $stmt->execute([
        'limit' => $params['limit'] ?? 50,
        'offset' => $params['offset'] ?? 0
    ]);
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Cache for 5 minutes
    SimpleCache::set($cacheKey, $products, 300);
    header('X-Cache: MISS');
    
    return $products;
}
```

## Scaling Milestones & Actions

### Phase 1: Launch (0-1,000 users/day)
✅ Current setup is fine
- Render free/starter tier
- Single PHP server
- File-based sessions

### Phase 2: Growth (1,000-10,000 users/day)
⚠️ Need optimizations:
```bash
# Add Redis for sessions and caching
composer require predis/predis

# Update session handler
ini_set('session.save_handler', 'redis');
ini_set('session.save_path', 'tcp://127.0.0.1:6379');
```

### Phase 3: Scale (10,000-50,000 users/day)
🚨 Infrastructure changes needed:
- Upgrade Render to Pro tier (more instances)
- Move PHP API to auto-scaling platform
- Add database read replicas
- Implement CDN for API responses

### Phase 4: High Traffic (50,000+ users/day)
🚀 Full architecture revision:
- Microservices for heavy endpoints
- Message queue for orders (RabbitMQ/SQS)
- Elasticsearch for product search
- Multiple database shards

## Monitoring with Render

### Add to React App:
```javascript
// Add error tracking
import * as Sentry from "@sentry/react";

Sentry.init({
  dsn: process.env.REACT_APP_SENTRY_DSN,
  environment: process.env.NODE_ENV,
  integrations: [
    new Sentry.BrowserTracing(),
  ],
  tracesSampleRate: 0.1, // 10% of requests
});

// Performance monitoring
export const measureApiCall = async (name, apiCall) => {
  const startTime = performance.now();
  try {
    const result = await apiCall();
    const duration = performance.now() - startTime;
    
    // Send to analytics
    if (window.gtag) {
      window.gtag('event', 'timing_complete', {
        name: name,
        value: Math.round(duration),
        event_category: 'API'
      });
    }
    
    return result;
  } catch (error) {
    // Track errors
    Sentry.captureException(error);
    throw error;
  }
};
```

### Add to PHP API:
```php
// Simple performance logging
function logPerformance($endpoint, $startTime) {
    $duration = microtime(true) - $startTime;
    
    if ($duration > 1.0) { // Log slow requests
        error_log(sprintf(
            "SLOW_REQUEST: %s took %.2f seconds",
            $endpoint,
            $duration
        ));
    }
    
    // Add header for monitoring
    header('X-Response-Time: ' . round($duration * 1000) . 'ms');
}
```

## Load Testing Before Launch

```bash
# Install Apache Bench
apt-get install apache2-utils

# Test your API endpoints
# Start small
ab -n 100 -c 10 https://api.yourdomain.com/v1/products/list.php

# Increase load
ab -n 1000 -c 50 https://api.yourdomain.com/v1/products/list.php

# Test with authentication
ab -n 100 -c 10 -H "Cookie: PHPSESSID=your-session-id" \
   https://api.yourdomain.com/v1/cart/get.php
```

## Critical Metrics to Watch

1. **Render Dashboard**
   - Request count
   - Response times
   - Error rates
   - Bandwidth usage

2. **PHP API Server**
   - CPU usage (keep < 70%)
   - Memory usage (keep < 80%)
   - PHP-FPM pool status
   - Error log size

3. **MySQL Database**
   - Connection count (< max_connections)
   - Slow query log
   - Query cache hit rate
   - Table lock waits

## Quick Fixes for Common Issues

### "Too Many Connections" Database Error
```sql
-- Increase MySQL connections
SET GLOBAL max_connections = 500;

-- Add to my.cnf
[mysqld]
max_connections = 500
```

### Slow API Response Times
```php
// Add query optimization hints
$stmt = $pdo->prepare("
    SELECT /*+ INDEX(Items idx_category_status) */ *
    FROM Items 
    WHERE category_id = :cat AND status = 'active'
");
```

### Memory Issues on PHP Server
```ini
; php.ini adjustments
memory_limit = 256M
max_execution_time = 30
opcache.enable = 1
opcache.memory_consumption = 256
```

## Emergency Scaling Checklist

If you suddenly get featured/viral traffic:

1. **Immediate (< 1 hour):**
   - [ ] Enable Render auto-scaling to max
   - [ ] Increase PHP server capacity (vertical scaling)
   - [ ] Enable all caching
   - [ ] Temporarily disable non-critical features

2. **Short-term (< 24 hours):**
   - [ ] Add Cloudflare in front of API
   - [ ] Set up Redis for sessions
   - [ ] Add more PHP servers behind load balancer
   - [ ] Enable read-only mode if needed

3. **Long-term (< 1 week):**
   - [ ] Migrate to auto-scaling infrastructure
   - [ ] Implement proper caching layer
   - [ ] Add database replicas
   - [ ] Set up monitoring and alerts

## Cost Optimization Tips

1. **Use Render's build cache** - Faster deployments
2. **Implement API response caching** - Reduce PHP server load
3. **Use CDN for product images** - Don't serve from API
4. **Batch API requests** - Reduce round trips
5. **Implement pagination properly** - Don't load all data

## Support Contacts

- Render Support: support@render.com
- Render Status: status.render.com
- Your API monitoring: [Set up UptimeRobot or Pingdom]