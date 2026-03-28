<?php

namespace K3Progetti\JwtBundle\Helper;

use Carbon\Carbon;
use K3Progetti\JwtBundle\Exception\JwtAuthorizationException;
use K3Progetti\JwtBundle\Mailer\TwoFactorMailerInterface;
use K3Progetti\JwtBundle\Repository\JwtUserRepositoryInterface;
use K3Progetti\JwtBundle\Security\JwtUserInterface;
use K3Progetti\JwtBundle\Service\JwtRefreshService;
use K3Progetti\JwtBundle\Service\JwtService;
use Random\RandomException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class AuthHelper
{

    public function __construct(
        private UserPasswordHasherInterface $passwordEncoder,
        private JwtService                  $jwtService,
        private JwtRefreshService           $jwtRefreshService,
        private ParameterBagInterface       $parameterBag,
        private JwtUserRepositoryInterface  $userRepository,
        private ?TwoFactorMailerInterface   $twoFactorMailer = null
    )
    {
    }

    /**
     * Validate User
     * @param JwtUserInterface|null $user
     * @return JsonResponse|null
     */
    public function validateUser(?JwtUserInterface $user = null): ?JsonResponse
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
     * @param JwtUserInterface|null $user
     * @return void
     */
    public function ensureUserExists(?JwtUserInterface $user): void
    {
        if (!$user) {
            throw new JwtAuthorizationException('Utente non trovato', Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Verifico che l'utente sia attivo.
     *
     * @param JwtUserInterface $user
     * @param int|null $companyId
     * @return void
     */
    public function ensureUserIsActive(JwtUserInterface $user, ?int $companyId = null): void
    {
        if (!$user->isActive()) {
            throw new JwtAuthorizationException('Account disabilitato', Response::HTTP_LOCKED);
        }
    }

    /**
     * Verifico la password
     * @param JwtUserInterface $user
     * @param string $password
     * @return JsonResponse|null
     */
    public function validatePassword(JwtUserInterface $user, string $password): ?JsonResponse
    {
        if (!$this->passwordEncoder->isPasswordValid($user, $password)) {
            throw new JwtAuthorizationException('Password non valida', Response::HTTP_UNAUTHORIZED);
        }

        return null;
    }

    /**
     * Verifico che il codice utente è valido
     *
     * @param JwtUserInterface $user
     * @param string|null $code2fa
     * @return void
     */
    public function validate2fa(JwtUserInterface $user, ?string $code2fa = null): void
    {
        if ($code2fa !== $user->getTwoFactorAuthCode()) {
            throw new JwtAuthorizationException('Codice non valido', Response::HTTP_LOCKED);
        }
    }


    /**
     * Genero il codice 2fa
     *
     * @param JwtUserInterface $user
     * @return void
     * @throws RandomException
     */
    public function build2faCode(JwtUserInterface $user): void
    {
        // Genero il codice
        $code = $this->generateNumeric2FA();
        $codeExpired = Carbon::now()->addMinutes($this->parameterBag->get('jwt.2fa_expired_code'));

        $user->setTwoFactorAuthCode($code);
        $user->setTwoFactorAuthCodeExpired($codeExpired);

        $this->userRepository->save($user);

        // Invio L'email
        $this->twoFactorMailer?->sendTwoFactorCode($user, $code);
    }


    /**
     * Verifico che l'utente sia attivo.
     *
     * @param JwtUserInterface $user
     * @param int|null $companyId
     * @return void
     */
    public function ensureUserRoles(JwtUserInterface $user, ?int $companyId = null): void
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
     * @param JwtUserInterface $user
     * @param Request $request
     * @param bool|null $deleteRefreshToken
     * @param string|null $oldRefreshToken
     * @return array
     * @throws RandomException
     */
    public function buildTokenResponse(
        JwtUserInterface $user,
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
    private function generateNumeric2FA(int $digits = 6): string
    {
        $min = (int)pow(10, $digits - 1);
        $max = (int)pow(10, $digits) - 1;
        return (string)random_int($min, $max);
    }
}
