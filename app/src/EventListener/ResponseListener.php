<?php

namespace App\EventListener;

use Framework\Event\ResponseEvent;

class ResponseListener
{
    public function __invoke(ResponseEvent $event): void
    {
        $event->getResponse()->headers->set('X-Developer', 'Evgeny');
    }
}
