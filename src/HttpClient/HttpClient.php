<?php

declare(strict_types=1);

namespace Ipedis\HttpSignature\HttpClient;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use Ipedis\HttpSignature\Signature\Signer;

trait HttpClient
{
    use Signer;

    protected ?Client $client = null;

    public function getClient(): Client
    {
        if (is_null($this->client)) {
            $this->client = new Client(['handler' => $this->getHandlerStack()]);
        }

        return $this->client;
    }

    /**
     * Create new Guzzle middleware stack
     */
    private function getHandlerStack(): HandlerStack
    {
        $stack = HandlerStack::create();
        $stack->push($this->addPSHeaders());

        return $stack;
    }

    /**
     * Add Custom PS headers to request
     */
    private function addPSHeaders(): Closure
    {
        return fn (callable $handler): Closure => function (Request $request, array $options) use ($handler) {
            $request = $this->sign($request);

            return $handler($request, $options);
        };
    }
}
