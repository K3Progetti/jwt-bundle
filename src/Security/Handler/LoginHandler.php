<?php

namespace K3Progetti\JwtBundle\Security\Handler;

use K3Progetti\JwtBundle\Helper\AuthHelper;
use App\Repository\UserRepository;
use JsonException;
use Random\RandomException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LoginHandler
{
    private UserRepository $userRepository;
    private AuthHelper $authHelper;

    public function __construct(
        UserRepository    $userRepository,
        AuthHelper $authHelper
    )
    {
        $this->userRepository = $userRepository;
        $this->authHelper = $authHelper;
    }

    /**
     * Handler della login
     *
     * @param Request $request
     * @param bool|null $twoFactorAuth
     * @return JsonResponse
     * @throws JsonException
     * @throws RandomException
     */
    public function handle(Request $request, ?bool $twoFactorAuth = false): JsonResponse
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $user = $this->userRepository->findOneBy(['username' => $data['username'] ?? null]);

        $this->authHelper->validateUser($user);

        $this->authHelper->validatePassword($user, $data['password'] ?? '');

        // Verifico il codice
        if (!empty($data['code2fa'])) {
            $this->authHelper->validate2fa($user, $data['code2fa']);

        } else if ($twoFactorAuth) { // Se viene richiesto verifico se è abilitato
            if ($user->isTwoFactorAuth()) {

                $this->authHelper->build2faCode($user);
                return new JsonResponse([
                    'twoFactorAuth' => true
                ]);
            }
        }

        $response = $this->authHelper->buildTokenResponse($user, $request);

        return new JsonResponse($response);
    }
}
