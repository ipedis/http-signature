<?php

namespace Ipedis\HttpSignature\Signature;


use Psr\Http\Message\RequestInterface;

/**
 * Represents the parts that makes up the signature
 *  <method>.<url>.<timestamp>.<body>
 *
 * Class SigningString
 * @package Ipedis\HttpSignature\Signature
 */
class SigningString
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $message;

    /**
     * @var int
     */
    private int $timestamp;

    /**
     * SigningString constructor.
     *
     * @param RequestInterface $message
     * @param int $timestamp
     */
    public function __construct(RequestInterface $message, int $timestamp)
    {
        $this->message = $message;
        $this->timestamp = $timestamp;
    }

    public function __toString()
    {
        return $this->string();
    }

    /**
     * Signature is in the format
     * <method>.<url>.<timestamp>.<body>
     *
     * @return string
     */
    public function string(): string
    {
        return sprintf("%s.%s.%d.%s",
            $this->message->getMethod(),
            $this->prepareUri((string) $this->message->getUri()),
            $this->timestamp,
            (string)$this->message->getBody()
        );
    }

    /**
     * @param string $uri
     * @return string
     */
    private function prepareUri(string $uri): string
    {
        /**
         * Remove any trailing slash
         */
        return rtrim($uri,"/");
    }
}
