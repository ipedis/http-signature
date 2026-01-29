<?php

declare(strict_types=1);

namespace Ipedis\Demo\HttpSignature\Command;


use GuzzleHttp\Exception\GuzzleException;
use JetBrains\PhpStorm\NoReturn;

class ExpiredCommand extends CommandAbstract implements CommandInterface
{

    /**
     * @throws GuzzleException
     */
    #[NoReturn] public function execute(string $baseUrl): void
    {
        printf("Calling EXPIRED GET webhook \n");

        $response = $this->getClient()->get(sprintf('%s/%s', $baseUrl, 'expired'));

        var_dump($response->getBody()->getContents());
        exit;
    }
}
