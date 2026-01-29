<?php

declare(strict_types=1);

namespace Ipedis\Demo\HttpSignature\Command;


use JetBrains\PhpStorm\NoReturn;

class DeleteCommand extends CommandAbstract implements CommandInterface
{

    #[NoReturn] public function execute(string $baseUrl): void
    {
        printf("Calling DELETE webhook \n");

        $response = $this->getClient()->delete(sprintf('%s/%s', $baseUrl, 'delete'));

        var_dump($response->getBody()->getContents());
        exit;
    }
}
