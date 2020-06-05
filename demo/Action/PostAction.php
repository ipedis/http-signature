<?php

namespace Ipedis\Demo\HttpSignature\Action;


use Symfony\Component\HttpFoundation\Request;

class PostAction extends ActionAbstract implements ActionInterface
{

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
