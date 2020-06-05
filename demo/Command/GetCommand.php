<?php

namespace Ipedis\Demo\HttpSignature\Command;


class GetCommand extends CommandAbstract implements CommandInterface
{

    public function execute(string $baseUrl)
    {
        printf("Calling GET webhook \n");

        $response = $this->getClient()->get(sprintf('%s/%s', $baseUrl, 'get'));

        var_dump($response->getBody()->getContents());
        exit;
    }
}
