<?php

namespace Ipedis\Demo\HttpSignature\Action;

use Symfony\Component\HttpFoundation\Request;

interface ActionInterface
{
    public function run(Request $request);
}
