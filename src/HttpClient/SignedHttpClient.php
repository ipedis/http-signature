<?php

declare(strict_types=1);

namespace Ipedis\HttpSignature\HttpClient;

use Carbon\Carbon;
use Ipedis\HttpSignature\Signature\Signature;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

/**
 * Symfony HTTP Client decorator that automatically signs outgoing requests.
 *
 * Wraps any HttpClientInterface and injects PS-Signature / PS-Timestamp headers.
 */
final readonly class SignedHttpClient implements HttpClientInterface
{
    public function __construct(
        private HttpClientInterface $client,
        private string $signatureKey,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $timestamp = Carbon::now()->getTimestamp();
        $body = $this->resolveBody($options);

        $psr17Factory = new Psr17Factory();
        $psrRequest = $psr17Factory->createRequest($method, $url);

        if ($body !== '') {
            $psrRequest = $psrRequest->withBody($psr17Factory->createStream($body));
        }

        $signature = new Signature($psrRequest, $this->signatureKey, $timestamp);

        /** @var array<string, string> $headers */
        $headers = $options['headers'] ?? [];
        $headers[Signature::PS_SIGNATURE_TIMESTAMP] = (string) $timestamp;
        $headers[Signature::PS_SIGNATURE_SIGNATURE] = (string) $signature;
        $options['headers'] = $headers;

        return $this->client->request($method, $url, $options);
    }

    public function stream(ResponseInterface|iterable $responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->client->stream($responses, $timeout);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function withOptions(array $options): static
    {
        return new self($this->client->withOptions($options), $this->signatureKey);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function resolveBody(array $options): string
    {
        if (isset($options['body']) && is_string($options['body'])) {
            return $options['body'];
        }

        if (isset($options['json'])) {
            return json_encode($options['json'], JSON_THROW_ON_ERROR);
        }

        return '';
    }
}
