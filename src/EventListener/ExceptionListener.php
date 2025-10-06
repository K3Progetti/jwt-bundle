<?php

namespace K3Progetti\JwtBundle\EventListener;

use App\Utils\Result;
use K3Progetti\JwtBundle\Exception\JwtAuthorizationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof JwtAuthorizationException) {
            $result = new Result();
            $result->setMessage($exception->getMessage());

            $response = new JsonResponse($result, 422);

            $event->setResponse($response);
        }

        // puoi gestire qui anche altre eccezioni se vuoi
    }
}