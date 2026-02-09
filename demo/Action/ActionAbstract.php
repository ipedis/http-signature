<?php

declare(strict_types=1);

namespace Ipedis\Demo\HttpSignature\Action;

use Ipedis\HttpSignature\Signature\Signature;
use Ipedis\HttpSignature\Signature\Verifier;
use Symfony\Component\HttpFoundation\Request;

abstract class ActionAbstract
{
    use Verifier;

    protected function getSignatureKey(): string
    {
        return '6dac31a13e50777a35bd3c7ac53823d7ac313e75';
    }

    protected function onValidMessage(Request $request): void
    {
        echo "Request is valid\n";
        echo sprintf("PS-Timestamp = %s \n", $request->headers->get(Signature::PS_SIGNATURE_TIMESTAMP));
        echo sprintf("PS-Signature = %s \n", $request->headers->get(Signature::PS_SIGNATURE_SIGNATURE));
    }

    protected function onInvalidMessage(): void
    {
        echo "Request is invalid\n";
    }
}
