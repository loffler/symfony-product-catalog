<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
        public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // some advanced error handling could be done here
        $response = new JsonResponse([
            'error' => [
                'message' => $exception->getMessage(),
            ]
        ]);
        $event->setResponse($response);
    }
}