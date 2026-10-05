<?php

declare(strict_types=1);

namespace Ipedis\HttpSignature\Signature;

/**
 * Injectable service for verifying HTTP request signatures.
 *
 * A valid request:
 * - has signature headers 'PS-Timestamp' and 'PS-Signature'
 * - has a timestamp within the last 60 seconds
 * - has a matching HMAC-SHA256 hash when recomputed
 */
class SignatureVerifier
{
    use Verifier;

    public function __construct(private readonly string $signatureKey)
    {
    }

    protected function getSignatureKey(): string
    {
        return $this->signatureKey;
    }
}
