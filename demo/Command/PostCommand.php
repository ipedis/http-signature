<?php

declare(strict_types=1);

namespace Ipedis\Demo\HttpSignature\Command;

use GuzzleHttp\Exception\GuzzleException;
use JetBrains\PhpStorm\NoReturn;

class PostCommand extends CommandAbstract implements CommandInterface
{
    /**
     * @throws GuzzleException
     */
    #[NoReturn]
    public function execute(string $baseUrl): void
    {
        printf("Calling POST webhook \n");

        $response = $this->getClient()->post(sprintf('%s/%s', $baseUrl, 'post'), [
            'form_params' => [
                'name' => 'Hello World',
                'email' => 'hello@world.com',
            ],
        ]);

        var_dump($response->getBody()->getContents());
        exit;
    }
}
