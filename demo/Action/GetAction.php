<?php

namespace Ipedis\Demo\HttpSignature\Action;

use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\HttpFoundation\Request;

class GetAction extends ActionAbstract implements ActionInterface
{
    #[NoReturn] public function run(Request $request): void
    {
        if ($this->verify($request)) {
            $this->onValidMessage($request);
        } else {
            $this->onInvalidMessage();
        }

        exit;
    }
}
