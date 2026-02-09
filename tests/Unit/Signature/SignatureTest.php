<?php

declare(strict_types=1);

namespace Ipedis\HttpSignature\Tests\Unit\Signature;

use Ipedis\HttpSignature\Signature\Signature;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SignatureTest extends TestCase
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
    public function signature_generates_hash(): void
    {
        $request = $this->psr17Factory->createRequest('POST', 'https://example.com/api/test');
        $request = $request->withBody($this->psr17Factory->createStream('{"test": "data"}'));

        $timestamp = 1234567890;
        $key = 'test-secret-key';

        $signature = new Signature($request, $key, $timestamp);
        $signatureString = $signature->string();

        $this->assertNotEmpty($signatureString);
        $this->assertEquals(64, strlen($signatureString)); // SHA256 produces 64 character hex string
    }

    /**
     */
    #[Test]
    public function signature_is_deterministic(): void
    {
        $request = $this->psr17Factory->createRequest('GET', 'https://example.com/api/users');

        $timestamp = 1234567890;
        $key = 'test-secret-key';

        $signature1 = new Signature($request, $key, $timestamp);
        $signature2 = new Signature($request, $key, $timestamp);

        $this->assertEquals($signature1->string(), $signature2->string());
    }

    /**
     */
    #[Test]
    public function signature_is_equal_method(): void
    {
        $request = $this->psr17Factory->createRequest('GET', 'https://example.com/api/test');

        $timestamp = 1234567890;
        $key = 'test-secret-key';

        $signature = new Signature($request, $key, $timestamp);
        $signatureString = $signature->string();

        $this->assertTrue($signature->isEqual($signatureString));
        $this->assertFalse($signature->isEqual('invalid-signature'));
    }

    /**
     */
    #[Test]
    public function signature_to_string(): void
    {
        $request = $this->psr17Factory->createRequest('GET', 'https://example.com/api/test');

        $timestamp = 1234567890;
        $key = 'test-secret-key';

        $signature = new Signature($request, $key, $timestamp);

        $this->assertEquals($signature->string(), (string) $signature);
    }

    /**
     */
    #[Test]
    public function different_timestamps_produce_different_signatures(): void
    {
        $request = $this->psr17Factory->createRequest('GET', 'https://example.com/api/test');
        $key = 'test-secret-key';

        $signature1 = new Signature($request, $key, 1234567890);
        $signature2 = new Signature($request, $key, 1234567891);

        $this->assertNotEquals($signature1->string(), $signature2->string());
    }

    /**
     */
    #[Test]
    public function different_keys_produce_different_signatures(): void
    {
        $request = $this->psr17Factory->createRequest('GET', 'https://example.com/api/test');
        $timestamp = 1234567890;

        $signature1 = new Signature($request, 'key1', $timestamp);
        $signature2 = new Signature($request, 'key2', $timestamp);

        $this->assertNotEquals($signature1->string(), $signature2->string());
    }

    /**
     */
    #[Test]
    public function signature_accepts_string_timestamp(): void
    {
        $request = $this->psr17Factory->createRequest('GET', 'https://example.com/api/test');
        $key = 'test-secret-key';

        $signature1 = new Signature($request, $key, 1234567890);
        $signature2 = new Signature($request, $key, '1234567890');

        $this->assertEquals($signature1->string(), $signature2->string());
    }
}
