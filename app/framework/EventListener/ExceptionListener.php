<?php

namespace Framework\EventListener;

use Framework\ExceptionHandler\ExceptionHandlerInterface;
use Framework\Event\ExceptionEvent;

class ExceptionListener
{
    private ExceptionHandlerInterface $handler;

    public function __construct(ExceptionHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param ExceptionEvent $event
     * @return void
     */
    public function __invoke(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        $request = $event->getRequest();
        $response = $this->handler->handleException($throwable, $request);
        $event->setResponse($response);
    }
}
