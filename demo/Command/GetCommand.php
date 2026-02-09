<?php

declare(strict_types=1);

namespace Ipedis\Demo\HttpSignature\Command;

use JetBrains\PhpStorm\NoReturn;

class GetCommand extends CommandAbstract implements CommandInterface
{
    #[NoReturn]
    public function execute(string $baseUrl): void
    {
        printf("Calling GET webhook \n");

        $response = $this->getClient()->get(sprintf('%s/%s', $baseUrl, 'get'));

        var_dump($response->getBody()->getContents());
        exit;
    }
}
