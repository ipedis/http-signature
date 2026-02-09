<?php

declare(strict_types=1);

use Ipedis\Demo\HttpSignature\Command\DeleteCommand;
use Ipedis\Demo\HttpSignature\Command\ExpiredCommand;
use Ipedis\Demo\HttpSignature\Command\GetCommand;
use Ipedis\Demo\HttpSignature\Command\InvalidPostCommand;
use Ipedis\Demo\HttpSignature\Command\PostCommand;

require __DIR__ . '/../vendor/autoload.php';

$baseUrl = sprintf('%s://%s', 'http', 'localhost:5000');

if (isset($argv[1]) && ($argv[1] !== '' && $argv[1] !== '0')) {
    switch ($argv[1]) {
        case 'get':
            (new GetCommand())->execute($baseUrl);

            break;
        case 'post':
            (new PostCommand())->execute($baseUrl);

            break;
        case 'delete':
            (new DeleteCommand())->execute($baseUrl);

            break;
        case 'expired':
            (new ExpiredCommand())->execute($baseUrl);

            break;
        case 'invalid-post':
            (new InvalidPostCommand())->execute($baseUrl);

            break;
    }
}
