<?php

namespace App\EventListener;

use framework\Event\ResponseEvent;

class ResponseListener
{
    public function __invoke(ResponseEvent $event): void
    {
        $event->getResponse()->headers->set('X-Developer', 'Evgeny');
    }
}
