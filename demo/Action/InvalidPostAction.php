<?php

declare(strict_types=1);

namespace Ipedis\Demo\HttpSignature\Action;

use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\HttpFoundation\Request;

class InvalidPostAction extends ActionAbstract implements ActionInterface
{
    /**
     * On this scenario, signature headers are not present
     */
    #[NoReturn]
    public function run(Request $request): void
    {
        if ($this->verify($request)) {
            $this->onValidMessage($request);
        } else {
            $this->onInvalidMessage();
        }

        exit;
    }
}
