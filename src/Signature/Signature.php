<?php


namespace Ipedis\HttpSignature\Signature;


use Psr\Http\Message\RequestInterface;

class Signature
{
    const PS_SIGNATURE_DIGEST_NAME = 'sha256';
    const PS_SIGNATURE_TIMESTAMP = 'PS-Timestamp';
    const PS_SIGNATURE_SIGNATURE = 'PS-Signature';

    /**
     * @var string
     */
    private string $key;

    /**
     * @var SigningString
     */
    private SigningString $signingString;

    /**
     * Signature constructor.
     *
     * @param RequestInterface $message
     * @param string $key
     * @param int $timestamp
     */
    public function __construct(RequestInterface $message, string $key, int $timestamp)
    {
        $this->key = $key;
        $this->signingString = new SigningString($message, $timestamp);
    }

    public function __toString()
    {
        return $this->string();
    }

    /**
     * Generate hash for signature
     *
     * @return string
     */
    public function string(): string
    {
        return hash_hmac(self::PS_SIGNATURE_DIGEST_NAME, (string)$this->signingString, $this->key);
    }

    /**
     * Check if signature is same
     *
     * @param string $signature
     * @return bool
     */
    public function isEqual(string $signature): bool
    {
        return hash_equals($this->string(), $signature);
    }
}
