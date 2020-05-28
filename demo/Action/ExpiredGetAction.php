<?php

namespace Ipedis\Demo\HttpSignature\Action;


use Symfony\Component\HttpFoundation\Request;

class ExpiredGetAction extends ActionAbstract implements ActionInterface
{
    /**
     * On this scenario, signature timestamp are older than 1 minute
     *
     * @param Request $request
     */
    public function run(Request $request)
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
