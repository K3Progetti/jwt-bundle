<?php

namespace K3Progetti\JwtBundle\Helper;

use Carbon\Carbon;
use K3Progetti\JwtBundle\Exception\JwtAuthorizationException;
use K3Progetti\JwtBundle\Service\JwtRefreshService;
use K3Progetti\JwtBundle\Service\JwtService;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\External\PostmarkService;
use Random\RandomException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthHelper
{

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordEncoder,
        private readonly JwtService                  $jwtService,
        private readonly JwtRefreshService           $jwtRefreshService,
        private readonly ParameterBagInterface       $parameterBag,
        private readonly UserRepository              $userRepository,
        private readonly PostmarkService             $postmarkService
    )
    {
    }

    /**
     * Validate User
     * @param User|null $user
     * @return JsonResponse|null
     */
    public
    function validateUser(?User $user = null): ?JsonResponse
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
    public
    function ensureUserExists(?User $user): void
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
    public
    function ensureUserIsActive(User $user, ?int $companyId = null): void
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
    public
    function validatePassword(User $user, string $password): ?JsonResponse
    {
        if (!$this->passwordEncoder->isPasswordValid($user, $password)) {
            throw new JwtAuthorizationException('Password non valida', Response::HTTP_UNAUTHORIZED);
        }

        return null;
    }

    /**
     * Verifico che il codice utente è valido
     *
     * @param User $user
     * @param string|null $code2fa
     * @return void
     */
    public
    function validate2fa(User $user, ?string $code2fa = null): void
    {
        if ($code2fa !== $user->getTwoFactorAuthCode()) {
            throw new JwtAuthorizationException('Codice non valido', Response::HTTP_LOCKED);
        }
    }


    /**
     * Genero il codice 2fa
     *
     * @param User $user
     * @return void
     * @throws RandomException
     */
    public
    function build2faCode(User $user): void
    {
        // Genero il codice
        $code = $this->generateNumeric2FA();
        $codeExpired = Carbon::now()->addMinutes($this->parameterBag->get('jwt.2fa_expired_code'));

        $user->setTwoFactorAuthCode($code);
        $user->setTwoFactorAuthCodeExpired($codeExpired);

        $this->userRepository->save($user);

        // Invio L'email
        $this->postmarkService->sendTwoFactorCode($user, $code);
    }


    /**
     * Verifico che l'utente sia attivo.
     *
     * @param User $user
     * @param int|null $companyId
     * @return void
     */
    public
    function ensureUserRoles(User $user, ?int $companyId = null): void
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
    public
    function buildTokenResponse(
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

    /**
     * @param int $digits
     * @return string
     * @throws RandomException
     */
    private
    function generateNumeric2FA(int $digits = 6): string
    {
        $min = (int)pow(10, $digits - 1);
        $max = (int)pow(10, $digits) - 1;
        return (string)random_int($min, $max);
    }
}
