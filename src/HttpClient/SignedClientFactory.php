<?php

declare(strict_types=1);

namespace Ipedis\HttpSignature\HttpClient;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Ipedis\HttpSignature\Signature\Signature;
use Carbon\Carbon;
use Psr\Http\Message\RequestInterface;

/**
 * Factory for creating Guzzle clients with automatic request signing.
 *
 * Designed for Laravel service container binding or standalone usage.
 */
class SignedClientFactory
{
    public function __construct(private readonly string $signatureKey)
    {
    }

    /**
     * @param array<string, mixed> $config Additional Guzzle client configuration
     */
    public function create(array $config = []): Client
    {
        $stack = HandlerStack::create();
        $stack->push($this->signatureMiddleware());

        return new Client(array_merge($config, ['handler' => $stack]));
    }

    private function signatureMiddleware(): Closure
    {
        return fn (callable $handler): Closure => function (RequestInterface $request, array $options) use ($handler) {
            $timestamp = Carbon::now()->getTimestamp();
            $signature = new Signature($request, $this->signatureKey, $timestamp);

            $request = $request->withHeader(Signature::PS_SIGNATURE_TIMESTAMP, (string) $timestamp);
            $request = $request->withHeader(Signature::PS_SIGNATURE_SIGNATURE, (string) $signature);

            return $handler($request, $options);
        };
    }
}
