<?php

declare(strict_types=1);

namespace Ipedis\HttpSignature\Signature;

use Psr\Http\Message\RequestInterface;

class Signature implements \Stringable
{
    public const PS_SIGNATURE_DIGEST_NAME = 'sha256';

    public const PS_SIGNATURE_TIMESTAMP = 'PS-Timestamp';

    public const PS_SIGNATURE_SIGNATURE = 'PS-Signature';

    private readonly SigningString $signingString;

    /**
     * Signature constructor.
     */
    public function __construct(RequestInterface $message, private readonly string $key, int|string $timestamp)
    {
        if (is_string($timestamp)) {
            $timestamp = (int) $timestamp;
        }
        $this->signingString = new SigningString($message, $timestamp);
    }

    public function __toString(): string
    {
        return $this->string();
    }

    /**
     * Generate hash for signature
     */
    public function string(): string
    {
        return hash_hmac(self::PS_SIGNATURE_DIGEST_NAME, (string) $this->signingString, $this->key);
    }

    /**
     * Check if signature is same
     */
    public function isEqual(string $signature): bool
    {
        return hash_equals($this->string(), $signature);
    }
}
