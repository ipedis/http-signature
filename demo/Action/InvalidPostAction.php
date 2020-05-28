<?php

namespace Ipedis\Demo\HttpSignature\Action;


use Symfony\Component\HttpFoundation\Request;

class InvalidPostAction extends ActionAbstract implements ActionInterface
{
    /**
     * On this scenario, signature headers are not present
     *
     * @param Request $request
     */
    public function run(Request $request)
    {
        if ($this->verify($request)) {
            $this->onValidMessage($request);
        } else {
            $this->onInvalidMessage();
        }

        exit;
    }
}
