<?php

declare(strict_types=1);

namespace Ipedis\HttpSignature\Tests\Unit\Signature;

use Carbon\Carbon;
use Ipedis\HttpSignature\Signature\Signature;
use Ipedis\HttpSignature\Signature\Signer;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SignerTest extends TestCase
{
    private Psr17Factory $psr17Factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->psr17Factory = new Psr17Factory();
    }

    /**
     */
    #[Test]
    public function signer_adds_signature_headers(): void
    {
        $signer = new class () {
            use Signer;

            protected function getSignatureKey(): string
            {
                return 'test-secret-key';
            }
        };

        $request = $this->psr17Factory->createRequest('POST', 'https://example.com/api/test');

        $signedRequest = $signer->sign($request);

        $this->assertTrue($signedRequest->hasHeader(Signature::PS_SIGNATURE_TIMESTAMP));
        $this->assertTrue($signedRequest->hasHeader(Signature::PS_SIGNATURE_SIGNATURE));
    }

    /**
     */
    #[Test]
    public function signer_timestamp_is_current(): void
    {
        $signer = new class () {
            use Signer;

            protected function getSignatureKey(): string
            {
                return 'test-secret-key';
            }
        };

        $request = $this->psr17Factory->createRequest('GET', 'https://example.com/api/test');

        $beforeSign = Carbon::now()->getTimestamp();
        $signedRequest = $signer->sign($request);
        $afterSign = Carbon::now()->getTimestamp();

        $timestamp = (int) $signedRequest->getHeader(Signature::PS_SIGNATURE_TIMESTAMP)[0];

        $this->assertGreaterThanOrEqual($beforeSign, $timestamp);
        $this->assertLessThanOrEqual($afterSign, $timestamp);
    }

    /**
     */
    #[Test]
    public function signer_signature_is_not_empty(): void
    {
        $signer = new class () {
            use Signer;

            protected function getSignatureKey(): string
            {
                return 'test-secret-key';
            }
        };

        $request = $this->psr17Factory->createRequest('POST', 'https://example.com/api/test');
        $request = $request->withBody($this->psr17Factory->createStream('{"test": "data"}'));

        $signedRequest = $signer->sign($request);

        $signature = $signedRequest->getHeader(Signature::PS_SIGNATURE_SIGNATURE)[0];

        $this->assertNotEmpty($signature);
        $this->assertEquals(64, strlen($signature)); // SHA256 produces 64 character hex string
    }

    /**
     */
    #[Test]
    public function signer_preserves_original_request_properties(): void
    {
        $signer = new class () {
            use Signer;

            protected function getSignatureKey(): string
            {
                return 'test-secret-key';
            }
        };

        $originalRequest = $this->psr17Factory->createRequest('POST', 'https://example.com/api/test');
        $originalRequest = $originalRequest->withBody($this->psr17Factory->createStream('{"test": "data"}'));

        $signedRequest = $signer->sign($originalRequest);

        $this->assertEquals($originalRequest->getMethod(), $signedRequest->getMethod());
        $this->assertEquals((string) $originalRequest->getUri(), (string) $signedRequest->getUri());
        $this->assertEquals((string) $originalRequest->getBody(), (string) $signedRequest->getBody());
    }
}
