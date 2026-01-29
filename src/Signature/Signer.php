<?php

declare(strict_types=1);

namespace Ipedis\HttpSignature\Signature;


use Psr\Http\Message\RequestInterface;
use Carbon\Carbon;

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
     *
     */
    public function sign(RequestInterface $message): RequestInterface
    {
        $timestamp = Carbon::now()->getTimestamp();
        $signature = new Signature($message, $this->getSignatureKey(), $timestamp);

        $message = $message->withAddedHeader(Signature::PS_SIGNATURE_TIMESTAMP, (string)$timestamp);

        return $message->withAddedHeader(Signature::PS_SIGNATURE_SIGNATURE, (string)$signature);
    }

    abstract protected function getSignatureKey(): string;
}
