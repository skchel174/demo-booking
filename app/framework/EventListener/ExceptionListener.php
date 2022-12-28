<?php

namespace Framework\EventListener;

use Framework\ExceptionHandler\ExceptionHandlerInterface;
use Framework\Event\ExceptionEvent;

class ExceptionListener
{
    public function __construct(private readonly ExceptionHandlerInterface $handler) {}

    /**
     * @param ExceptionEvent $event
     * @return void
     */
    public function __invoke(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        $request = $event->getRequest();
        $response = $this->handler->handle($throwable, $request);
        $event->setResponse($response);
    }
}
