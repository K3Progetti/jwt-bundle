<?php

namespace K3Progetti\JwtBundle\Service;

use K3Progetti\JwtBundle\Entity\JwtToken;
use K3Progetti\JwtBundle\Repository\JwtTokenRepository;
use K3Progetti\JwtBundle\Security\JwtUserInterface;
use Carbon\Carbon;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class JwtService
{
    private string $secret;
    private JwtTokenRepository $jwtTokenRepository;
    private int $expirationTime;
    private string $algorithm;
    private iterable $overrideModifiers;
    private iterable $afterModifiers;
    private iterable $beforeModifiers;
    private string $timeZone;

    public function __construct(
        ParameterBagInterface $params,
        JwtTokenRepository    $jwtTokenRepository,
        iterable              $beforeModifiers = [],
        iterable              $afterModifiers = [],
        iterable              $overrideModifiers = []
    )
    {

        $this->expirationTime = $params->get('jwt.token_ttl');
        $this->secret = $params->get('jwt.secret_key');
        $this->algorithm = $params->get('jwt.algorithm');
        $this->timeZone = $params->get('jwt.time_zone');
        $this->jwtTokenRepository = $jwtTokenRepository;
        $this->overrideModifiers = $overrideModifiers;
        $this->afterModifiers = $afterModifiers;
        $this->beforeModifiers = $beforeModifiers;
    }

    /**
     * Genero un jwt Token
     * @param array $payload
     * @param string|null $userAgent
     * @param string|null $ipAddress
     * @param string|null $externalToken
     * @return string
     */
    public function createToken(array $payload, ?string $userAgent = null, ?string $ipAddress = null, ?string $externalToken = null): string
    {
        $expiredAt = Carbon::now($this->timeZone)
            ->setTimezone($this->timeZone)
            ->addSeconds($this->expirationTime); // Imposta la scadenza
        $payload['expiredAt'] = $expiredAt->timestamp;


        $jwt = JWT::encode($payload, $this->secret, $this->algorithm);
        if (!empty($externalToken)) {
            $jwt = $externalToken;
        }

        $jwtToken = new JwtToken();
        $jwtToken->setToken($jwt);
        $jwtToken->setUsername($payload['username']);
        $jwtToken->setCreatedAt(Carbon::now()->toDateTimeImmutable());
        $jwtToken->setExpiredAt($expiredAt->toDateTimeImmutable());
        $jwtToken->setDevice($userAgent);
        $jwtToken->setIpAddress($ipAddress);

        $this->jwtTokenRepository->save($jwtToken);

        return $jwt;
    }

    /**
     * Genero un jwt Token
     * @param string $token
     * @return void
     */
    public function removeToken(string $token): void
    {

        $jwtToken = $this->jwtTokenRepository->findOneBy(['token' => $token]);
        if ($jwtToken) {
            $this->jwtTokenRepository->remove($jwtToken);
        }

    }


    /**
     * Decodifico e verifico un token JWT
     */
    public function decodeToken(string $token): ?array
    {
        try {
            return (array)JWT::decode($token, new Key($this->secret, $this->algorithm));
        } catch (Exception $e) {
            return null; // Token non valido
        }
    }

    /**
     * Creo il payload per il JWT
     * @param JwtUserInterface $user
     * @return array
     */
    public function getPayload(JwtUserInterface $user): array
    {

        // 1. Verifico se devo fare un ovverride
        foreach ($this->overrideModifiers as $override) {
            if (method_exists($override, 'overridePayload')) {
                $custom = $override->overridePayload($user);
                if ($custom !== null) {
                    return $custom;
                }
            }
        }

        $payload = [];
        // In caso di Before
        foreach ($this->beforeModifiers as $before) {
            if (method_exists($before, 'onBeforePayload')) {
                $payload = $before->onBeforePayload($user);
            }
        }

        // Payload Base
        $payload = array_merge($payload, [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'name' => $user->getName(),
            'surname' => $user->getSurname(),
            'roles' => $user->getRoles(),
        ]);

        // Dopo la costruzione di quello base
        foreach ($this->afterModifiers as $after) {
            if (method_exists($after, 'onAfterPayload')) {
                $payload = $after->onAfterPayload($payload, $user);
            }
        }

        return $payload;
    }

}
