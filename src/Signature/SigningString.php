<?php

declare(strict_types=1);

namespace Ipedis\HttpSignature\Signature;

use Psr\Http\Message\RequestInterface;

/**
 * Represents the parts that makes up the signature
 *  <method>.<url>.<timestamp>.<body>
 *
 * Class SigningString
 */
readonly class SigningString implements \Stringable
{
    /**
     * SigningString constructor.
     */
    public function __construct(private RequestInterface $message, private int $timestamp)
    {
    }

    public function __toString(): string
    {
        return $this->string();
    }

    /**
     * Signature is in the format
     * <method>.<url>.<timestamp>.<body>
     */
    public function string(): string
    {
        return sprintf(
            '%s.%s.%d.%s',
            $this->message->getMethod(),
            $this->prepareUri((string) $this->message->getUri()),
            $this->timestamp,
            (string) $this->message->getBody()
        );
    }

    private function prepareUri(string $uri): string
    {
        /**
         * Remove any trailing slash
         */
        return rtrim($uri, '/');
    }
}
