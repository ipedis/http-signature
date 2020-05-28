<?php

namespace Ipedis\Demo\HttpSignature\Command;


use GuzzleHttp\Client;

class InvalidPostCommand extends CommandAbstract implements CommandInterface
{
    /**
     * In this scenario, we are using directly guzzle
     * which will not include signature headers to the request
     *
     * @param string $baseUrl
     */
    public function execute(string $baseUrl)
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
