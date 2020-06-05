<?php

namespace Ipedis\Demo\HttpSignature\Command;


class ExpiredCommand extends CommandAbstract implements CommandInterface
{

    /**
     * @param string $baseUrl
     */
    public function execute(string $baseUrl)
    {
        printf("Calling EXPIRED GET webhook \n");

        $response = $this->getClient()->get(sprintf('%s/%s', $baseUrl, 'expired'));

        var_dump($response->getBody()->getContents());
        exit;
    }
}
