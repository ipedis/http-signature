<?php

declare(strict_types=1);

namespace Ipedis\HttpSignature\Tests\Unit\HttpClient;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Ipedis\HttpSignature\HttpClient\SignedClientFactory;
use Ipedis\HttpSignature\Signature\Signature;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class SignedClientFactoryTest extends TestCase
{
    #[Test]
    public function it_creates_a_guzzle_client(): void
    {
        $factory = new SignedClientFactory('test-key');
        $client = $factory->create();

        $this->assertInstanceOf(Client::class, $client);
    }

    #[Test]
    public function it_accepts_custom_guzzle_config(): void
    {
        $factory = new SignedClientFactory('test-key');
        $client = $factory->create(['timeout' => 30]);

        $this->assertInstanceOf(Client::class, $client);
    }

    #[Test]
    public function it_signs_requests_with_correct_headers(): void
    {
        $capturedRequest = null;

        $mock = new MockHandler([
            function (RequestInterface $request) use (&$capturedRequest): Response {
                $capturedRequest = $request;

                return new Response(200);
            },
        ]);

        $factory = new SignedClientFactory('test-key');
        $middleware = $this->getMiddleware($factory);

        $stack = HandlerStack::create($mock);
        $stack->push($middleware);

        $client = new Client(['handler' => $stack]);
        $client->post('https://example.com/api/test', [
            'body' => '{"event": "test"}',
        ]);

        $this->assertInstanceOf(RequestInterface::class, $capturedRequest);
        $this->assertTrue($capturedRequest->hasHeader(Signature::PS_SIGNATURE_TIMESTAMP));
        $this->assertTrue($capturedRequest->hasHeader(Signature::PS_SIGNATURE_SIGNATURE));
        $this->assertEquals(64, strlen($capturedRequest->getHeader(Signature::PS_SIGNATURE_SIGNATURE)[0]));
    }

    #[Test]
    public function signed_request_can_be_verified(): void
    {
        $key = 'shared-secret';
        $capturedRequest = null;

        $mock = new MockHandler([
            function (RequestInterface $request) use (&$capturedRequest): Response {
                $capturedRequest = $request;

                return new Response(200);
            },
        ]);

        $factory = new SignedClientFactory($key);
        $middleware = $this->getMiddleware($factory);

        $stack = HandlerStack::create($mock);
        $stack->push($middleware);

        $client = new Client(['handler' => $stack]);
        $client->post('https://example.com/api/test', [
            'body' => '{"event": "test"}',
        ]);

        $this->assertInstanceOf(RequestInterface::class, $capturedRequest);

        $timestamp = $capturedRequest->getHeader(Signature::PS_SIGNATURE_TIMESTAMP)[0];
        $receivedSignature = $capturedRequest->getHeader(Signature::PS_SIGNATURE_SIGNATURE)[0];

        $rawRequest = $capturedRequest
            ->withoutHeader(Signature::PS_SIGNATURE_TIMESTAMP)
            ->withoutHeader(Signature::PS_SIGNATURE_SIGNATURE);

        $expectedSignature = new Signature($rawRequest, $key, $timestamp);

        $this->assertTrue($expectedSignature->isEqual($receivedSignature));
    }

    private function getMiddleware(SignedClientFactory $factory): Closure
    {
        $reflection = new \ReflectionClass($factory);
        $method = $reflection->getMethod('signatureMiddleware');

        /** @var Closure $middleware */
        $middleware = $method->invoke($factory);

        return $middleware;
    }
}
