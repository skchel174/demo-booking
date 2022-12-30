<?php

namespace Framework\Event;

use Symfony\Component\HttpFoundation\Request;

class ExceptionEvent extends RequestEvent
{
    private \Throwable $throwable;

    public function __construct(\Throwable $throwable, Request $request)
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
