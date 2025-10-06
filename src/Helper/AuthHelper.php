<?php

namespace K3Progetti\JwtBundle\Helper;

use K3Progetti\JwtBundle\Exception\JwtAuthorizationException;
use K3Progetti\JwtBundle\Service\JwtRefreshService;
use K3Progetti\JwtBundle\Service\JwtService;
use App\Entity\User;
use Random\RandomException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthHelper
{
    private UserPasswordHasherInterface $passwordEncoder;
    private JwtService $jwtService;
    private JwtRefreshService $jwtRefreshService;

    public function __construct(
        UserPasswordHasherInterface $passwordEncoder,
        JwtService                  $jwtService,
        JwtRefreshService           $jwtRefreshService
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->jwtService = $jwtService;
        $this->jwtRefreshService = $jwtRefreshService;
    }

    /**
     * Validate User
     * @param User|null $user
     * @return JsonResponse|null
     */
    public function validateUser(?User $user = null): ?JsonResponse
    {
        if (!$user) {
            throw new JwtAuthorizationException('Credenziali non valide', Response::HTTP_UNAUTHORIZED);
        }

        $this->ensureUserIsActive($user);

        return null;
    }

    /**
     * Verifico che l'utente esista.
     *
     * @param User|null $user
     * @return void
     * @throws JwtAuthorizationException
     */
    public function ensureUserExists(?User $user): void
    {
        if (!$user) {
            throw new JwtAuthorizationException('Utente non trovato', Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Verifico che l'utente sia attivo.
     *
     * @param User $user
     * @param int|null $companyId
     * @return void
     */
    public function ensureUserIsActive(User $user, ?int $companyId = null): void
    {
        if (!$user->isActive()) {
            throw new JwtAuthorizationException('Account disabilitato', Response::HTTP_LOCKED);
        }
    }

    /**
     * Verifico la password
     * @param User $user
     * @param string $password
     * @return JsonResponse|null
     */
    public function validatePassword(User $user, string $password): ?JsonResponse
    {
        if (!$this->passwordEncoder->isPasswordValid($user, $password)) {
            throw new JwtAuthorizationException('Password non valida', Response::HTTP_UNAUTHORIZED);
        }

        return null;
    }

    /**
     * Verifico che l'utente sia attivo.
     *
     * @param User $user
     * @param int|null $companyId
     * @return void
     */
    public function ensureUserRoles(User $user, ?int $companyId = null): void
    {
        if ($companyId !== null) {
            // TODO ...

        } else {
            if (!$user->isActive()) {
                throw new JwtAuthorizationException('Account disabilitato', Response::HTTP_LOCKED);
            }
        }
    }

    /**
     * @param User $user
     * @param Request $request
     * @param bool|null $deleteRefreshToken
     * @param string|null $oldRefreshToken
     * @return array
     * @throws RandomException
     */
    public function buildTokenResponse(
        User    $user,
        Request $request,
        ?bool   $deleteRefreshToken = false,
        ?string $oldRefreshToken = null
    ): array
    {

        $userAgent = $request->headers->get('User-Agent');
        $ip = $request->getClientIp();

        $payload = $this->jwtService->getPayload($user);

        $accessToken = $this->jwtService->createToken($payload, $userAgent, $ip);

        if ($deleteRefreshToken) {
            $this->jwtRefreshService->deleteRefreshToken($oldRefreshToken);
        }

        if (!$deleteRefreshToken && !empty($oldRefreshToken)) {
            $refreshToken = $oldRefreshToken;
        } else {
            $refreshToken = $this->jwtRefreshService->createRefreshToken($user, $userAgent, $ip);
        }

        return [
            'token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }
}
