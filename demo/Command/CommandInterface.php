<?php

declare(strict_types=1);

namespace Ipedis\Demo\HttpSignature\Command;


interface CommandInterface
{
    public function execute(string $baseUrl);
}
