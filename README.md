## Limitr Documentation

### Installation

#### Requirements
* PHP 7.0 or higher
* Redis

#### Install via Composer
```
composer require hbrawnak/limitr
```

### Configuration

#### 1. Choose a Storage Driver

Redis Storage
```
$redis = new \Redis();
$redis->connect('127.0.0.1');
$storage = new \Hbrawnak\Limitr\Drivers\RedisStorage($redis);
```

#### 2. Create Rate Limit Rules
```
use Hbrawnak\Limitr\Rules\RateLimitRule;

$rules = [
    new RateLimitRule('api-ip-limit', 100, 60, 'ip', 3600, 5),
    new RateLimitRule('user-limit', 1000, 3600, 'user_id')
];
```

### Basic Usage

#### 1. Implement Request Context
```
class MyRequestContext implements \Hbrawnak\Limitr\Contracts\RequestContext
{
    public function getIp(): string
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    public function getUserId(): ?string
    {
        return Auth::id() ?? null;
    }

    public function getEndpoint(): string
    {
        return $_SERVER['REQUEST_URI'];
    }
}
```

#### 2. Initialize Rate Limiter

```
use Hbrawnak\Limitr\RateLimiter;
use Hbrawnak\Limitr\Blocklist;

$blocklist = new Blocklist($storage);
$limiter = new RateLimiter($storage, $blocklist, $rules);
```

#### 3. Apply Rate Limiting
```
try {
    $context = new MyRequestContext();
    $limiter->check($context);
    
    // Application logic here
    
} catch (\Hbrawnak\Limitr\Exceptions\RateLimitExceededException $e) {
    http_response_code(429);
    foreach ($e->getHeaders() as $name => $value) {
        header("$name: $value");
    }
    exit;
}
```

### Advanced Configuration

#### Multiple Rules Combination

```
$rules = [
    // Global IP limit
    new RateLimitRule('global-ip', 1000, 3600, 'ip'),
    
    // Endpoint-specific limit
    new RateLimitRule('login-endpoint', 5, 60, 'endpoint'),
    
    // User-based limit
    new RateLimitRule('premium-users', 10000, 86400, 'user_id', 86400, 3)
];
```

#### Manual Block Management
```
// Block an IP
$blocklist->block('ip:203.0.113.42', 86400);

// Remove block
$blocklist->removeBlock('ip:203.0.113.42');

// Check block status
if ($blocklist->isBlocked('ip:203.0.113.42')) {
    // Handle blocked request
}
```


#### Laravel Middleware Example

```
namespace App\Http\Middleware;

use Closure;
use SmartApi\RateLimiter\RateLimiter;

class RateLimitMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            app(RateLimiter::class)->check(
                new class($request) implements RequestContext {
                    // Implement context methods
                }
            );
        } catch (RateLimitExceededException $e) {
            return response()->json([
                'error' => 'Too many requests'
            ], 429)->withHeaders($e->getHeaders());
        }

        return $next($request);
    }
}
```

### Author:
[Md Habibur Rahman](https://habib.im)

