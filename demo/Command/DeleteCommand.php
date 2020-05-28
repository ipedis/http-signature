<?php

namespace Ipedis\Demo\HttpSignature\Command;


class DeleteCommand extends CommandAbstract implements CommandInterface
{

    public function execute(string $baseUrl)
    {
        printf("Calling DELETE webhook \n");

        $response = $this->getClient()->delete(sprintf('%s/%s', $baseUrl, 'delete'));

        var_dump($response->getBody()->getContents());
        exit;
    }
}
