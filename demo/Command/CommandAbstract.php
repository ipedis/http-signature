<?php

declare(strict_types=1);

namespace Ipedis\Demo\HttpSignature\Command;


use Ipedis\HttpSignature\HttpClient\HttpClient;

abstract class CommandAbstract
{
    use HttpClient;

    protected function getSignatureKey(): string
    {
        return '6dac31a13e50777a35bd3c7ac53823d7ac313e75';
    }
}
