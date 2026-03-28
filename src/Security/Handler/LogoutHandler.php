<?php

namespace K3Progetti\JwtBundle\Security\Handler;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LogoutHandler
{
    /**
     * Per il logout
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        // TODO da implementare
        return new JsonResponse(['data' => null]);
    }
}