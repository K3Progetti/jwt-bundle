<?php

namespace K3Progetti\JwtBundle\Service;

use K3Progetti\JwtBundle\JwtPayloadInterface;
use K3Progetti\JwtBundle\Entity\JwtToken;
use K3Progetti\JwtBundle\Repository\JwtTokenRepository;
use App\Entity\User;
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

    public function __construct(
        ParameterBagInterface $params,
        JwtTokenRepository    $jwtTokenRepository,
        iterable $beforeModifiers = [],
        iterable $afterModifiers = [],
        iterable $overrideModifiers = []
    )
    {

        $this->expirationTime = $params->get('jwt.token_ttl');
        $this->secret = $params->get('jwt.secret_key');
        $this->algorithm = $params->get('jwt.algorithm');
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
        $expiredAt = Carbon::now()->addSeconds($this->expirationTime); // Imposta la scadenza
        $payload['expiredAt'] = $expiredAt->timestamp;


        $jtw = JWT::encode($payload, $this->secret, $this->algorithm);
        if (!empty($externalToken)) {
            $jtw = $externalToken;
        }

        $jtwToken = new JwtToken();
        $jtwToken->setToken($jtw);
        $jtwToken->setUsername($payload['username']);
        $jtwToken->setCreatedAt(Carbon::now()->toDateTimeImmutable());
        $jtwToken->setExpiredAt($expiredAt->toDateTimeImmutable());
        $jtwToken->setDevice($userAgent);
        $jtwToken->setIpAddress($ipAddress);

        $this->jwtTokenRepository->save($jtwToken);

        return $jtw;
    }

    /**
     * Genero un jwt Token
     * @param string $token
     * @return void
     */
    public function removeToken(string $token): void
    {

        $jtwToken = $this->jwtTokenRepository->findOneBy(['token' => $token]);
        if ($jtwToken) {
            $this->jwtTokenRepository->remove($jtwToken);
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
     * @param User $user
     * @return array
     */
    public function getPayload(User $user): array
    {

        // 1. Verifico se devo fare un ovverride
        foreach ($this->overrideModifiers as $override) {
            $custom = $override->override($user);
            if ($custom !== null) {
                return $custom;
            }
        }

        $payload = [];
        // In caso di Before
        foreach ($this->beforeModifiers as $before) {
            $payload = array_merge($payload, $before->before($user));
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
            $payload = $after->after($payload, $user);
        }

        return $payload;
    }

}
