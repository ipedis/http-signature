# HTTP Signature

[![CI](https://github.com/ipedis/http-signature/actions/workflows/ci.yml/badge.svg)](https://github.com/ipedis/http-signature/actions/workflows/ci.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/ipedis/http-signature.svg)](https://packagist.org/packages/ipedis/http-signature)
[![PHP Version](https://img.shields.io/packagist/php-v/ipedis/http-signature.svg)](https://packagist.org/packages/ipedis/http-signature)
[![License](https://img.shields.io/packagist/l/ipedis/http-signature.svg)](https://packagist.org/packages/ipedis/http-signature)

HMAC-SHA256 HTTP request signing and verification library for PHP. Signs outgoing PSR-7 requests and verifies incoming ones using a shared secret key, with built-in replay attack protection (60-second window).

## Installation

```bash
composer require ipedis/http-signature
```

## Quick Start

### Sign outgoing requests (Guzzle middleware)

```php
use Ipedis\HttpSignature\HttpClient\HttpClient;

class MyApiClient
{
    use HttpClient;

    protected function getSignatureKey(): string
    {
        return 'your-shared-secret-key';
    }
}

$client = new MyApiClient();
$response = $client->getClient()->post('https://api.example.com/webhook', [
    'json' => ['event' => 'user.created'],
]);
// PS-Signature and PS-Timestamp headers are added automatically
```

### Verify incoming requests

```php
use Ipedis\HttpSignature\Signature\Verifier;
use Symfony\Component\HttpFoundation\Request;

class WebhookController
{
    use Verifier;

    protected function getSignatureKey(): string
    {
        return 'your-shared-secret-key';
    }

    public function handle(Request $request): void
    {
        if (!$this->verify($request)) {
            throw new \RuntimeException('Invalid signature');
        }

        // Request is authentic and recent (< 60 seconds)
    }
}
```

## Framework Integration

The library provides injectable services as an alternative to traits, following each framework's dependency injection conventions.

### Symfony

The `SignedHttpClient` is a decorator that wraps any Symfony `HttpClientInterface` and automatically signs every outgoing request.

**Register the services:**

```yaml
# config/services.yaml
services:
    Ipedis\HttpSignature\HttpClient\SignedHttpClient:
        arguments:
            $client: '@http_client'
            $signatureKey: '%env(HTTP_SIGNATURE_KEY)%'

    Ipedis\HttpSignature\Signature\SignatureVerifier:
        arguments:
            $signatureKey: '%env(HTTP_SIGNATURE_KEY)%'
```

**Sign outgoing requests:**

```php
use Ipedis\HttpSignature\HttpClient\SignedHttpClient;

class WebhookDispatcher
{
    public function __construct(private SignedHttpClient $client) {}

    public function dispatch(string $url, array $payload): void
    {
        $this->client->request('POST', $url, [
            'json' => $payload,
        ]);
        // PS-Signature and PS-Timestamp headers are added automatically
    }
}
```

**Verify incoming requests:**

```php
use Ipedis\HttpSignature\Signature\SignatureVerifier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class WebhookController
{
    public function __construct(private SignatureVerifier $verifier) {}

    public function __invoke(Request $request): JsonResponse
    {
        if (!$this->verifier->verify($request)) {
            return new JsonResponse(['error' => 'Invalid signature'], 403);
        }

        // Process webhook...
        return new JsonResponse(['status' => 'ok']);
    }
}
```

### Laravel

The library ships with a service provider that auto-registers `SignedClientFactory` and `SignatureVerifier` as singletons.

**Step 1 — Add your signature key to config:**

```php
// config/services.php
return [
    // ...
    'http_signature' => [
        'key' => env('HTTP_SIGNATURE_KEY'),
    ],
];
```

**Step 2 — Register the service provider** (auto-discovered if using Laravel package discovery):

```php
// bootstrap/providers.php (Laravel 11+)
return [
    // ...
    Ipedis\HttpSignature\Laravel\HttpSignatureServiceProvider::class,
];
```

Or in `config/app.php` for older versions:

```php
'providers' => [
    // ...
    Ipedis\HttpSignature\Laravel\HttpSignatureServiceProvider::class,
],
```

**Sign outgoing requests:**

```php
use Ipedis\HttpSignature\HttpClient\SignedClientFactory;

class WebhookDispatcher
{
    public function __construct(private SignedClientFactory $factory) {}

    public function dispatch(string $url, array $payload): void
    {
        $client = $this->factory->create();
        $client->post($url, [
            'json' => $payload,
        ]);
        // PS-Signature and PS-Timestamp headers are added automatically
    }
}
```

**Verify incoming requests:**

```php
use Ipedis\HttpSignature\Signature\SignatureVerifier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WebhookController
{
    public function __construct(private SignatureVerifier $verifier) {}

    public function __invoke(Request $request): JsonResponse
    {
        if (!$this->verifier->verify($request)) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // Process webhook...
        return response()->json(['status' => 'ok']);
    }
}
```

## How It Works

1. A **signing string** is built: `METHOD.URL.TIMESTAMP.BODY`
2. An HMAC-SHA256 hash is computed using the shared secret
3. Two headers are added to the request:
   - `PS-Timestamp` — Unix timestamp
   - `PS-Signature` — 64-char hex HMAC hash
4. On verification, the signature is recomputed and compared using constant-time `hash_equals()`
5. Requests older than **60 seconds** are rejected (replay protection)

## API

### Services (recommended)

| Class | Purpose |
|---|---|
| `HttpClient\SignedHttpClient` | Symfony `HttpClientInterface` decorator, auto-signs requests |
| `HttpClient\SignedClientFactory` | Factory creating Guzzle clients with signing middleware |
| `Signature\SignatureVerifier` | Verifies incoming request signatures (PSR-7 and Symfony) |
| `Laravel\HttpSignatureServiceProvider` | Laravel service provider for container registration |

### Traits (legacy)

| Trait | Purpose |
|---|---|
| `Signature\Signer` | Adds `sign(RequestInterface): RequestInterface` |
| `Signature\Verifier` | Adds `verify(Request\|RequestInterface): bool` |
| `HttpClient\HttpClient` | Guzzle middleware, auto-signs every request |

All traits require implementing `getSignatureKey(): string`.

### Core

| Class | Purpose |
|---|---|
| `Signature\Signature` | HMAC-SHA256 hash generator and comparator |
| `Signature\SigningString` | Builds the `METHOD.URL.TIMESTAMP.BODY` string |

## Compatibility

| PHP | Status |
|-----|--------|
| 8.2 | ✅ |
| 8.3 | ✅ |
| 8.4 | ✅ |
| 8.5 | ✅ |

| Symfony | Status |
|---------|--------|
| 6.4     | ✅ |
| 7.x     | ✅ |
| 8.x     | ✅ |

| Laravel | Status |
|---------|--------|
| 10.x    | ✅ |
| 11.x    | ✅ |
| 12.x    | ✅ |

## Local Development

Requires [Docker](https://www.docker.com/).

```bash
make up        # Start container
make install   # Install dependencies
make qa        # Run full QA suite (rector + pint + phpstan + tests)
```

Available targets:

| Command | Description |
|---------|-------------|
| `make up` | Start container |
| `make down` | Stop container |
| `make install` | Install Composer dependencies |
| `make update` | Update Composer dependencies |
| `make test` | Run PHPUnit tests |
| `make phpstan` | Run static analysis (level max) |
| `make pint` | Fix code style (PSR-12) |
| `make rector` | Run automated refactoring |
| `make qa` | Run all checks |
| `make shell` | Open container shell |

## Disclaimer

This package is maintained by [Ipedis](https://www.ipedis.com). It is provided as-is under the terms of its license.
