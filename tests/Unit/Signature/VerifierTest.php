<?php

declare(strict_types=1);

namespace Ipedis\HttpSignature\Tests\Unit\Signature;

use Carbon\Carbon;
use Ipedis\HttpSignature\Signature\Signature;
use Ipedis\HttpSignature\Signature\Signer;
use Ipedis\HttpSignature\Signature\Verifier;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

class VerifierTest extends TestCase
{
    private Psr17Factory $psr17Factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->psr17Factory = new Psr17Factory();
    }

    public function test_verifier_accepts_valid_signature(): void
    {
        $key = 'test-secret-key';

        $signer = new class($key) {
            use Signer;

            public function __construct(private string $key)
            {
            }

            protected function getSignatureKey(): string
            {
                return $this->key;
            }
        };

        $verifier = new class($key) {
            use Verifier;

            public function __construct(private string $key)
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

    public function test_verifier_rejects_missing_signature_headers(): void
    {
        $verifier = new class {
            use Verifier;

            protected function getSignatureKey(): string
            {
                return 'test-secret-key';
            }
        };

        $request = $this->psr17Factory->createRequest('POST', 'https://example.com/api/test');

        $this->assertFalse($verifier->verify($request));
    }

    public function test_verifier_rejects_invalid_signature(): void
    {
        $verifier = new class {
            use Verifier;

            protected function getSignatureKey(): string
            {
                return 'test-secret-key';
            }
        };

        $request = $this->psr17Factory->createRequest('POST', 'https://example.com/api/test');
        $request = $request->withHeader(Signature::PS_SIGNATURE_TIMESTAMP, (string) Carbon::now()->getTimestamp());
        $request = $request->withHeader(Signature::PS_SIGNATURE_SIGNATURE, 'invalid-signature');

        $this->assertFalse($verifier->verify($request));
    }

    public function test_verifier_rejects_expired_signature(): void
    {
        $key = 'test-secret-key';

        $verifier = new class($key) {
            use Verifier;

            public function __construct(private string $key)
            {
            }

            protected function getSignatureKey(): string
            {
                return $this->key;
            }
        };

        // Create a request with a timestamp from more than 1 minute ago
        $oldTimestamp = Carbon::now()->subMinutes(2)->getTimestamp();

        $request = $this->psr17Factory->createRequest('POST', 'https://example.com/api/test');

        // Manually add signature headers with old timestamp
        $signature = new Signature($request, $key, $oldTimestamp);
        $request = $request->withHeader(Signature::PS_SIGNATURE_TIMESTAMP, (string) $oldTimestamp);
        $request = $request->withHeader(Signature::PS_SIGNATURE_SIGNATURE, $signature->string());

        $this->assertFalse($verifier->verify($request));
    }

    public function test_verifier_rejects_wrong_key(): void
    {
        $signerKey = 'signer-key';
        $verifierKey = 'verifier-key';

        $signer = new class($signerKey) {
            use Signer;

            public function __construct(private string $key)
            {
            }

            protected function getSignatureKey(): string
            {
                return $this->key;
            }
        };

        $verifier = new class($verifierKey) {
            use Verifier;

            public function __construct(private string $key)
            {
            }

            protected function getSignatureKey(): string
            {
                return $this->key;
            }
        };

        $request = $this->psr17Factory->createRequest('POST', 'https://example.com/api/test');
        $signedRequest = $signer->sign($request);

        $this->assertFalse($verifier->verify($signedRequest));
    }
}
