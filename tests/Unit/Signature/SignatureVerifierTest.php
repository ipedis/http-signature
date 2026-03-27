<?php

declare(strict_types=1);

namespace Ipedis\HttpSignature\Tests\Unit\Signature;

use Carbon\Carbon;
use Ipedis\HttpSignature\Signature\Signature;
use Ipedis\HttpSignature\Signature\SignatureVerifier;
use Ipedis\HttpSignature\Signature\Signer;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SignatureVerifierTest extends TestCase
{
    private Psr17Factory $psr17Factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->psr17Factory = new Psr17Factory();
    }

    #[Test]
    public function it_accepts_valid_signature(): void
    {
        $key = 'test-secret-key';
        $verifier = new SignatureVerifier($key);

        $signer = new class ($key) {
            use Signer;

            public function __construct(private readonly string $key)
            {
            }

            protected function getSignatureKey(): string
            {
                return $this->key;
            }
        };

        $request = $this->psr17Factory->createRequest('POST', 'https://example.com/api/test');
        $request = $request->withBody($this->psr17Factory->createStream('{"test": "data"}'));

        $signedRequest = $signer->sign($request);

        $this->assertTrue($verifier->verify($signedRequest));
    }

    #[Test]
    public function it_rejects_missing_signature_headers(): void
    {
        $verifier = new SignatureVerifier('test-secret-key');

        $request = $this->psr17Factory->createRequest('POST', 'https://example.com/api/test');

        $this->assertFalse($verifier->verify($request));
    }

    #[Test]
    public function it_rejects_invalid_signature(): void
    {
        $verifier = new SignatureVerifier('test-secret-key');

        $request = $this->psr17Factory->createRequest('POST', 'https://example.com/api/test');
        $request = $request->withHeader(Signature::PS_SIGNATURE_TIMESTAMP, (string) Carbon::now()->getTimestamp());
        $request = $request->withHeader(Signature::PS_SIGNATURE_SIGNATURE, 'invalid-signature');

        $this->assertFalse($verifier->verify($request));
    }

    #[Test]
    public function it_rejects_expired_signature(): void
    {
        $key = 'test-secret-key';
        $verifier = new SignatureVerifier($key);

        $oldTimestamp = Carbon::now()->subMinutes(2)->getTimestamp();

        $request = $this->psr17Factory->createRequest('POST', 'https://example.com/api/test');

        $signature = new Signature($request, $key, $oldTimestamp);
        $request = $request->withHeader(Signature::PS_SIGNATURE_TIMESTAMP, (string) $oldTimestamp);
        $request = $request->withHeader(Signature::PS_SIGNATURE_SIGNATURE, $signature->string());

        $this->assertFalse($verifier->verify($request));
    }

    #[Test]
    public function it_rejects_wrong_key(): void
    {
        $signer = new class ('signer-key') {
            use Signer;

            public function __construct(private readonly string $key)
            {
            }

            protected function getSignatureKey(): string
            {
                return $this->key;
            }
        };

        $verifier = new SignatureVerifier('verifier-key');

        $request = $this->psr17Factory->createRequest('POST', 'https://example.com/api/test');
        $signedRequest = $signer->sign($request);

        $this->assertFalse($verifier->verify($signedRequest));
    }
}
