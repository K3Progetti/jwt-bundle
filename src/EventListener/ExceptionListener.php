<?php

namespace K3Progetti\JwtBundle\EventListener;

use K3Progetti\JwtBundle\Http\Result;
use K3Progetti\JwtBundle\Exception\JwtAuthorizationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\Response;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof JwtAuthorizationException) {
            $result = new Result();
            $result->setMessage($exception->getMessage());

            // Se non è impostato un codice valido, fallback a 401
            $statusCode = $exception->getCode() ?: Response::HTTP_UNAUTHORIZED;

            $response = new JsonResponse($result,  $statusCode);

            $event->setResponse($response);
        }

        // qua posso gestire altre opzioni
    }
}