<?php


namespace K3Progetti\JwtBundle\Controller;


use K3Progetti\JwtBundle\Security\Handler\LoginHandler;
use K3Progetti\JwtBundle\Security\Handler\LogoutHandler;
use K3Progetti\JwtBundle\Security\Handler\RefreshTokenHandler;
use JsonException;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


class AuthController extends AbstractController
{

    public function __construct()
    {
    }

    /**
     * @param Request $request
     * @param LoginHandler $handler
     * @return JsonResponse
     * @throws JsonException
     * @throws RandomException
     */
    #[Route('/login_check', name: 'login', methods: ['POST'])]
    public function login(Request $request, LoginHandler $handler): JsonResponse
    {
        return $handler->handle($request);
    }

    /**
     * @param Request $request
     * @param LoginHandler $handler
     * @return JsonResponse
     * @throws JsonException
     * @throws RandomException
     */
    #[Route('/login_check_2fa', name: 'login_2fa', methods: ['POST'])]
    public function login2FA(Request $request, LoginHandler $handler): JsonResponse
    {
        return $handler->handle($request, true);
    }


    /**
     * @param Request $request
     * @param RefreshTokenHandler $handler
     * @return JsonResponse
     * @throws JsonException
     * @throws RandomException
     */
    #[Route('/token/refresh', name: 'refresh_token', methods: 'POST')]
    public function refreshToken(Request $request, RefreshTokenHandler $handler): JsonResponse
    {
        return $handler->handle($request);
    }


    /**
     * @param Request $request
     * @param LogoutHandler $handler
     * @return JsonResponse
     */
    #[Route('/api/logout', name: 'logout', methods: 'GET')]
    public function getUsers(Request $request, LogoutHandler $handler): JsonResponse
    {

        return $handler->handle($request);
    }


}


