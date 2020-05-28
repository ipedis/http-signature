<?php

namespace Ipedis\HttpSignature\HttpClient;


use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use Ipedis\HttpSignature\Signature\Signer;

trait HttpClient
{
    use Signer;

    /**
     * @var Client|null
     */
    protected $client = null;

    public function getClient(): Client
    {
        if (is_null($this->client)) {
            $this->client = new Client(['handler' => $this->getHandlerStack()]);
        }

        return $this->client;
    }

    /**
     * Create new Guzzle middleware stack
     *
     * @return HandlerStack
     */
    private function getHandlerStack(): HandlerStack
    {
        $stack = HandlerStack::create();
        $stack->push($this->addPSHeaders());

        return $stack;
    }

    /**
     * Add Custom PS headers to request
     *
     * @return \Closure
     */
    private function addPSHeaders()
    {
        return function (callable $handler)
        {
            return function (Request $request, array $options) use ($handler) {
                $request = $this->sign($request);
                return $handler($request, $options);
            };
        };
    }
}
