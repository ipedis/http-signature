<?php

declare(strict_types=1);

namespace Ipedis\HttpSignature\Signature;

use Carbon\Carbon;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\RequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request;

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
    public function __construct(private readonly string $signatureKey)
    {
    }

    public function verify(Request|RequestInterface $message): bool
    {
        try {
            if ($message instanceof Request) {
                $psr17Factory = new Psr17Factory();
                $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
                $message = $psrHttpFactory->createRequest($message);
            }

            return $this->checkForValidSignature($message);
        } catch (\Exception) {
            return false;
        }
    }

    private function checkForValidSignature(RequestInterface $message): bool
    {
        if (
            !$message->hasHeader(Signature::PS_SIGNATURE_TIMESTAMP) ||
            !$message->hasHeader(Signature::PS_SIGNATURE_SIGNATURE)
        ) {
            return false;
        }

        $messageTimestamp = $message->getHeader(Signature::PS_SIGNATURE_TIMESTAMP)[0];
        $messageSignature = $message->getHeader(Signature::PS_SIGNATURE_SIGNATURE)[0];

        if ($this->isRequestExpired($messageTimestamp)) {
            return false;
        }

        $verificationSignature = new Signature($message, $this->signatureKey, $messageTimestamp);

        return $verificationSignature->isEqual($messageSignature);
    }

    private function isRequestExpired(int|string $requestTimestamp): bool
    {
        if (is_string($requestTimestamp)) {
            $requestTimestamp = (int) $requestTimestamp;
        }

        return (Carbon::now()->getTimestamp() - $requestTimestamp) > 60;
    }
}
