<?php

declare(strict_types=1);

namespace Ipedis\HttpSignature\Laravel;

use Illuminate\Support\ServiceProvider;
use Ipedis\HttpSignature\HttpClient\SignedClientFactory;
use Ipedis\HttpSignature\Signature\SignatureVerifier;

class HttpSignatureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SignedClientFactory::class, fn ($app): SignedClientFactory => new SignedClientFactory(
            (string) config('services.http_signature.key'),
        ));

        $this->app->singleton(SignatureVerifier::class, fn ($app): SignatureVerifier => new SignatureVerifier(
            (string) config('services.http_signature.key'),
        ));
    }
}
