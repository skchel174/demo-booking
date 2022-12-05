<?php

namespace Framework\Kernel\Exception;

class NotFoundHttpException extends \RuntimeException
{
    public function __construct(string $message = "")
    {
        parent::__construct($message, 404);
    }
}
