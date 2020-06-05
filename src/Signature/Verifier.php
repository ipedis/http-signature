<?php

namespace Ipedis\HttpSignature\Signature;


use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\RequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request;

/**
 * Utility to check if a request is valid. A valid request:
 * - has signature header 'PS-Timestamp' and 'PS-Signature'
 * - has a timestamp with less than 1 minute ago
 * - has same hash when signature is recomputed
 *
 * Trait Verifier
 * @package Ipedis\HttpSignature\Signature
 */
trait Verifier
{
    /**
     * Check for valid signatures
     *
     * @param $message
     * @return bool
     */
    public function verify($message): bool
    {
        try {
            /**
             * Symfony compatibility
             * Converts HttpFoundation request to compatible psr-7 message
             */
            if ($message instanceof Request) {
                $psr17Factory = new Psr17Factory();
                $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
                $message = $psrHttpFactory->createRequest($message);
            }

            /**
             * Message is not PSR-7 compatible
             */
            if (!$message instanceof RequestInterface) {
                throw new \Exception('Request is not compatible with PSR-7');
            }

            return $this->checkForValidSignature($message);
        } catch (\Exception $exception) {
            /**
             * Exception hook that can be overwritten by child classes
             */
            $this->onException($exception);
            return false;
        }
    }

    abstract protected function getSignatureKey(): string;

    /**
     * Exception hook
     * Can be overwritten by child classes
     *
     * @param \Exception $exception
     */
    protected function onException(\Exception $exception){ }

    /**
     * @param RequestInterface $message
     * @return bool
     */
    private function checkForValidSignature(RequestInterface $message): bool
    {
        /**
         * Signature headers are missing
         */
        if (
            !$message->hasHeader(Signature::PS_SIGNATURE_TIMESTAMP) ||
            !$message->hasHeader(Signature::PS_SIGNATURE_SIGNATURE)
        ) {
            return false;
        }

        $messageTimestamp = $message->getHeader(Signature::PS_SIGNATURE_TIMESTAMP)[0];
        $messageSignature = $message->getHeader(Signature::PS_SIGNATURE_SIGNATURE)[0];

        /**
         * Reject if timestamp older then 1 minute,
         * it can be Man to the middle who try to replay query
         */
        if ($this->isRequestExpired($messageTimestamp)) {
            return false;
        }

        /**
         * Check if hash matches
         */
        $verificationSignature = new Signature($message, $this->getSignatureKey(), $messageTimestamp);

        return $verificationSignature->isEqual($messageSignature);
    }

    /**
     * Reject if timestamp older then 1 minute,
     * it can be Man to the middle who try to replay query
     *
     * @param int $requestTimestamp
     * @return bool
     */
    private function isRequestExpired(int $requestTimestamp): bool
    {
        return (time() - $requestTimestamp) > 60;
    }
}
