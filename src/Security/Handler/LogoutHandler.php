<?php

namespace K3Progetti\JwtBundle\Security\Handler;

use App\Utils\Result;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LogoutHandler
{
    private Result $result;

    public function __construct(
        Result $result
    )
    {
        $this->result = $result;
    }

    /**
     * Per il logout
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            // TODO da implementare
            $this->result->setData(null);

            return new JsonResponse($this->result->toArray());
        } catch (Exception $e) {
            $this->result->setMessage($e->getMessage());
            return new JsonResponse($this->result->toArray(), 422);
        }
    }
}
