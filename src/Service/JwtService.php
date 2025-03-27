<?php

namespace K3Progetti\JwtBundle\Service;


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

    public function __construct(
        ParameterBagInterface $params,
        JwtTokenRepository    $jwtTokenRepository
    )
    {

        $this->expirationTime = $params->get('jwt.token_ttl');
        $this->secret = $params->get('jwt.secret_key');
        $this->algorithm = $params->get('jwt.algorithm');
        $this->jwtTokenRepository = $jwtTokenRepository;
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
     * Creo il payload per il JTW
     * @param User $user
     * @return array
     */
    public function getPayload(User $user): array
    {

        $payload = [];

        $payload['id'] = $user->getId();
        $payload['username'] = $user->getUsername();
        $payload['name'] = $user->getName();
        $payload['surname'] = $user->getSurname();
        $payload['roles'] = $user->getRoles();

        return $payload;
    }

}
