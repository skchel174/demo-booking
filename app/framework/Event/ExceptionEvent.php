<?php

namespace Framework\Event;

use Symfony\Component\HttpFoundation\Request;

class ExceptionEvent extends RequestEvent
{
    private \Throwable $throwable;
    private Request $request;

    public function __construct(Request $request, \Throwable $throwable)
    {
        parent::__construct($request);
        $this->throwable = $throwable;
    }

    /**
     * @return \Throwable
     */
    public function getThrowable(): \Throwable
    {
        return $this->throwable;
    }

    /**
     * @param \Throwable $throwable
     */
    public function setThrowable(\Throwable $throwable): void
    {
        $this->throwable = $throwable;
    }
}
