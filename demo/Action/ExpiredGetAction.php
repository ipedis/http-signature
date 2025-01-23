<?php

namespace Ipedis\Demo\HttpSignature\Action;


use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\HttpFoundation\Request;

class ExpiredGetAction extends ActionAbstract implements ActionInterface
{
    /**
     * On this scenario, signature timestamp are older than 1 minute
     *
     * @param Request $request
     */
    #[NoReturn] public function run(Request $request): void
    {
        sleep(61);

        if ($this->verify($request)) {
            $this->onValidMessage($request);
        } else {
            $this->onInvalidMessage();
        }

        exit;
    }
}
