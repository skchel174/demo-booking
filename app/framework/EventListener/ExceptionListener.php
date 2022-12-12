<?php

namespace Framework\EventListener;

use Framework\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\Response;

class ExceptionListener
{
    private bool $debug;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($this->debug) {
            dd($exception);
        }

        $event->setResponse(new Response('', Response::HTTP_INTERNAL_SERVER_ERROR));
    }
}
