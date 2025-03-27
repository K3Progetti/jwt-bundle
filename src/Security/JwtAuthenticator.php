<?php

namespace K3Progetti\JwtBundle\Security;

use K3Progetti\JwtBundle\Helper\AuthHelper;
use K3Progetti\JwtBundle\Repository\JwtTokenRepository;
use K3Progetti\JwtBundle\Service\JwtService;
use App\Repository\UserRepository;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class JwtAuthenticator extends AbstractAuthenticator
{
    private JwtService $jwtService;
    private UserRepository $userRepository;
    private JwtTokenRepository $jwtTokenRepository;
    private AuthHelper $authHelper;

    public function __construct(
        JwtService         $jwtService,
        UserRepository     $userRepository,
        JwtTokenRepository $jwtTokenRepository,
        AuthHelper         $authHelper
    )
    {
        $this->jwtService = $jwtService;
        $this->userRepository = $userRepository;
        $this->jwtTokenRepository = $jwtTokenRepository;
        $this->authHelper = $authHelper;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api/') &&
            $request->headers->has('Authorization');
    }

    /**
     * Autentifico
     * @param Request $request
     * @return SelfValidatingPassport
     */
    public function authenticate(Request $request): SelfValidatingPassport
    {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            throw new AuthenticationException('Token non presente', Response::HTTP_BAD_REQUEST);
        }

        $jwt = substr($authHeader, 7);
        $decodedToken = $this->jwtService->decodeToken($jwt);

        if (!$decodedToken) {
            throw new AuthenticationException('Token non valido', Response::HTTP_UNAUTHORIZED);
        }

        $jwtToken = $this->jwtTokenRepository->findOneBy(['token' => $jwt]);

        if ($jwtToken === null) {
            throw new AuthenticationException('Token non trovato', Response::HTTP_FORBIDDEN);
        }

        $expiredAt = $jwtToken->getExpiredAt();

        // Controllo se il token è scaduto con Carbon
        if (Carbon::now()->greaterThan($expiredAt)) {
            // Elimino il token scaduto
            $this->jwtService->removeToken($jwt);
            throw new AuthenticationException('Token scaduto', Response::HTTP_UNAUTHORIZED);
        }

        // Trovo l'utente nel database
        $user = $this->userRepository->findOneBy(['username' => $decodedToken['username'] ?? null]);

        // Verifico se esiste Esiste l'utente
        $this->authHelper->ensureUserExists($user);

        // E' Attivo
        $this->authHelper->ensureUserIsActive($user);

        return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier()));
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return JsonResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        // Ottiene il messaggio e il codice dell'eccezione
        $message = $exception->getMessage();
        $statusCode = $exception->getCode();

        // Se il codice HTTP non è valido, imposta un valore predefinito
        if (!in_array($statusCode, [400, 401, 403, 422, 423], true)) {
            $statusCode = Response::HTTP_UNAUTHORIZED; // 401 di default
        }

        return new JsonResponse(['message' => $message], $statusCode);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Se implementato Symfony si ferma e restituisce questa risposta
        return null;
    }
}
