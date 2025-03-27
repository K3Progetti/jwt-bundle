<?php

namespace K3Progetti\JwtBundle\Security\Handler;

use K3Progetti\JwtBundle\Helper\AuthHelper;
use K3Progetti\JwtBundle\Repository\JwtRefreshTokenRepository;
use Carbon\Carbon;
use JsonException;
use Random\RandomException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class RefreshTokenHandler
{
    private JwtRefreshTokenRepository $jwtRefreshTokenRepository;
    private AuthHelper $authHelper;

    public function __construct(
        JwtRefreshTokenRepository $jwtRefreshTokenRepository,
        AuthHelper $authHelper
    )
    {
        $this->jwtRefreshTokenRepository = $jwtRefreshTokenRepository;
        $this->authHelper = $authHelper;
    }

    /**
     * Refresh token Handles
     * @param Request $request
     * @return JsonResponse
     * @throws JsonException
     * @throws RandomException
     */
    public function handle(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $refreshToken = $data['refresh_token'] ?? null;

        if (!$refreshToken) {
            throw new AuthenticationException('Refresh token mancante', Response::HTTP_FORBIDDEN);
        }

        // Verifico se è scaduto oppure no
        $refreshTokenEntity = $this->jwtRefreshTokenRepository->isValidRefreshToken($refreshToken);

        // Verifico se è scaduto oppure no
        if (!$refreshTokenEntity) {
            return new JsonResponse(['message' => 'Refresh token non valido o scaduto'], Response::HTTP_UNAUTHORIZED);
        }

        $user = $refreshTokenEntity->getAppUser();

        // Verifico se è attivo oppure no
        $this->authHelper->validateUser($user);

        $expiresAt = $refreshTokenEntity->getExpiresAt();
        $now = Carbon::now()->toDateTimeImmutable();
        $interval = $expiresAt->diff($now);

        $deleteRefreshOld = $interval->days < 3;

        $response = $this->authHelper->buildTokenResponse($user, $request, $deleteRefreshOld, $refreshToken);

        return new JsonResponse($response);
    }
}

