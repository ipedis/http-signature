<?php

namespace Ipedis\Demo\HttpSignature\Command;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JetBrains\PhpStorm\NoReturn;

class InvalidPostCommand extends CommandAbstract implements CommandInterface
{
    /**
     * In this scenario, we are using directly guzzle
     * which will not include signature headers to the request
     *
     * @param string $baseUrl
     * @throws GuzzleException
     */
    #[NoReturn] public function execute(string $baseUrl): void
    {
        $client = new Client();

        $response = $client->post(sprintf('%s/%s', $baseUrl, 'post'), [
            'form_params' => [
                'name'  => 'Hello World',
                'email' => 'hello@world.com'
            ]
        ]);

        var_dump($response->getBody()->getContents());
        exit;
    }
}
