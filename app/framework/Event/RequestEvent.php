<?php

namespace Framework\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

class RequestEvent extends Event
{
    private Request $request;
    private Response $response;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return bool
     */
    public function hasResponse(): bool
    {
        return isset($this->response);
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @param Response $response
     * @return void
     */
    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }
}
