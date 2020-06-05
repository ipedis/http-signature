<?php

namespace Ipedis\Demo\HttpSignature\Command;


interface CommandInterface
{
    public function execute(string $baseUrl);
}
