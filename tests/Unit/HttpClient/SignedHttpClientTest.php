<?php

declare(strict_types=1);

namespace Ipedis\HttpSignature\Tests\Unit\HttpClient;

use Ipedis\HttpSignature\HttpClient\SignedHttpClient;
use Ipedis\HttpSignature\Signature\Signature;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class SignedHttpClientTest extends TestCase
{
    #[Test]
    public function it_implements_http_client_interface(): void
    {
        $inner = $this->createMock(HttpClientInterface::class);
        $client = new SignedHttpClient($inner, 'test-key');

        $this->assertInstanceOf(HttpClientInterface::class, $client);
    }

    #[Test]
    public function it_adds_signature_headers_to_request(): void
    {
        $inner = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $inner->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://example.com/api/test',
                $this->callback(function (array $options): bool {
                    $this->assertIsArray($options['headers']);

                    /** @var array<string, string> $headers */
                    $headers = $options['headers'];

                    $this->assertArrayHasKey(Signature::PS_SIGNATURE_TIMESTAMP, $headers);
                    $this->assertArrayHasKey(Signature::PS_SIGNATURE_SIGNATURE, $headers);
                    $this->assertEquals(64, strlen($headers[Signature::PS_SIGNATURE_SIGNATURE]));

                    return true;
                }),
            )
            ->willReturn($response);

        $client = new SignedHttpClient($inner, 'test-key');
        $result = $client->request('POST', 'https://example.com/api/test', [
            'body' => '{"event": "user.created"}',
        ]);

        $this->assertSame($response, $result);
    }

    #[Test]
    public function it_signs_request_with_json_body(): void
    {
        $inner = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $inner->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://example.com/api/test',
                $this->callback(function (array $options): bool {
                    /** @var array<string, string> $headers */
                    $headers = $options['headers'];

                    $this->assertArrayHasKey(Signature::PS_SIGNATURE_SIGNATURE, $headers);

                    return true;
                }),
            )
            ->willReturn($response);

        $client = new SignedHttpClient($inner, 'test-key');
        $client->request('POST', 'https://example.com/api/test', [
            'json' => ['event' => 'user.created'],
        ]);
    }

    #[Test]
    public function it_signs_request_with_empty_body(): void
    {
        $inner = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $inner->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://example.com/api/test',
                $this->callback(function (array $options): bool {
                    /** @var array<string, string> $headers */
                    $headers = $options['headers'];

                    $this->assertArrayHasKey(Signature::PS_SIGNATURE_SIGNATURE, $headers);
                    $this->assertEquals(64, strlen($headers[Signature::PS_SIGNATURE_SIGNATURE]));

                    return true;
                }),
            )
            ->willReturn($response);

        $client = new SignedHttpClient($inner, 'test-key');
        $client->request('GET', 'https://example.com/api/test');
    }

    #[Test]
    public function it_delegates_stream_to_inner_client(): void
    {
        $inner = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(ResponseStreamInterface::class);

        $inner->expects($this->once())
            ->method('stream')
            ->with($response, 30.0)
            ->willReturn($stream);

        $client = new SignedHttpClient($inner, 'test-key');
        $result = $client->stream($response, 30.0);

        $this->assertSame($stream, $result);
    }

    #[Test]
    public function it_preserves_existing_headers(): void
    {
        $inner = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $inner->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://example.com/api/test',
                $this->callback(function (array $options): bool {
                    /** @var array<string, string> $headers */
                    $headers = $options['headers'];

                    $this->assertEquals('application/json', $headers['Content-Type']);
                    $this->assertArrayHasKey(Signature::PS_SIGNATURE_SIGNATURE, $headers);

                    return true;
                }),
            )
            ->willReturn($response);

        $client = new SignedHttpClient($inner, 'test-key');
        $client->request('POST', 'https://example.com/api/test', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => '{}',
        ]);
    }

    #[Test]
    public function it_throws_on_unsupported_body_type(): void
    {
        $inner = $this->createMock(HttpClientInterface::class);
        $client = new SignedHttpClient($inner, 'test-key');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SignedHttpClient only supports string body or json options');

        $client->request('POST', 'https://example.com/api/test', [
            'body' => fopen('php://memory', 'r'),
        ]);
    }

    #[Test]
    public function with_options_returns_new_instance(): void
    {
        $inner = $this->createMock(HttpClientInterface::class);
        $newInner = $this->createMock(HttpClientInterface::class);

        $inner->expects($this->once())
            ->method('withOptions')
            ->with(['base_uri' => 'https://example.com'])
            ->willReturn($newInner);

        $client = new SignedHttpClient($inner, 'test-key');
        $newClient = $client->withOptions(['base_uri' => 'https://example.com']);

        $this->assertNotSame($client, $newClient);
        $this->assertInstanceOf(SignedHttpClient::class, $newClient);
    }
}
