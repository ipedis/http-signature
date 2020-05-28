<?php

namespace Ipedis\Demo\HttpSignature\Command;


class PostCommand extends CommandAbstract implements CommandInterface
{

    public function execute(string $baseUrl)
    {
        printf("Calling POST webhook \n");

        $response = $this->getClient()->post(sprintf('%s/%s', $baseUrl, 'post'), [
           'form_params' => [
               'name'  => 'Hello World',
               'email' => 'hello@world.com'
           ]
        ]);

        var_dump($response->getBody()->getContents());
        exit;
    }
}
