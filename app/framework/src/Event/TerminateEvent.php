<?php

namespace Framework\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TerminateEvent extends KernelEvent
{
    public function __construct(Request $request, private readonly Response $response)
    {
        parent::__construct($request);
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}