<?php

namespace Ipedis\HttpSignature\Signature;


use Psr\Http\Message\RequestInterface;

/**
 * Trait Signer
 *
 * @package Ipedis\HttpSignature\Signature
 *
 * Accepts a PSR-7 Message and adds signature headers to the message
 * - Current timestamp
 * - hash of <method>.<url>.<timestamp>.<body>
 *
 */
trait Signer
{
    /**
     * Add signature headers to the PSR-7 message
     *
     * @param RequestInterface $message
     *
     * @return RequestInterface
     */
    public function sign(RequestInterface $message): RequestInterface
    {
        $timestamp = time();
        $signature = new Signature($message, $this->getSignatureKey(), $timestamp);

        $message = $message->withAddedHeader(Signature::PS_SIGNATURE_TIMESTAMP, (string)$timestamp);
        $message = $message->withAddedHeader(Signature::PS_SIGNATURE_SIGNATURE, (string)$signature);

        return $message;
    }

    abstract protected function getSignatureKey(): string;
}
