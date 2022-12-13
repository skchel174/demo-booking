<?php

namespace Framework\Event;

use Symfony\Component\HttpFoundation\Response;

class RequestEvent extends KernelEvent
{
    private Response $response;

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
