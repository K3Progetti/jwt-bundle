<?php

namespace K3Progetti\JwtBundle\Service;

use K3Progetti\JwtBundle\Entity\JwtRefreshToken;
use K3Progetti\JwtBundle\Repository\JwtRefreshTokenRepository;
use App\Entity\User;
use Carbon\Carbon;
use Random\RandomException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class JwtRefreshService
{

    private JwtRefreshTokenRepository $jwtRefreshTokenRepository;

    private int $refreshTokenTtl;

    public function __construct(
        JwtRefreshTokenRepository $jwtRefreshTokenRepository,
        ParameterBagInterface     $params
    )
    {
        $this->jwtRefreshTokenRepository = $jwtRefreshTokenRepository;
        $this->refreshTokenTtl = $params->get('jwt.refresh_token_ttl');
    }

    /**
     * @param User $user
     * @param string|null $userAgent
     * @param string|null $ipAddress
     * @return string
     * @throws RandomException
     */
    public function createRefreshToken(User $user, ?string $userAgent = null, ?string $ipAddress = null): string
    {
        $refreshToken = bin2hex(random_bytes(64));

        // Salvo il refresh token nella tabella jwt_refresh
        $jwtRefreshToken = new JwtRefreshToken();
        $jwtRefreshToken->setAppUser($user);
        $jwtRefreshToken->setRefreshToken($refreshToken);
        $jwtRefreshToken->setCreatedAt(Carbon::now()->toDateTimeImmutable());
        $jwtRefreshToken->setExpiresAt(Carbon::now()->addSeconds($this->refreshTokenTtl)->toDateTimeImmutable());

        $jwtRefreshToken->setDevice($userAgent);
        $jwtRefreshToken->setIpAddress($ipAddress);

        $this->jwtRefreshTokenRepository->save($jwtRefreshToken);

        return $refreshToken;
    }

    /**
     * Elimino
     * @param string $refreshToken
     * @return void
     */
    public function deleteRefreshToken(string $refreshToken): void
    {
        $jwtRefreshToken = $this->jwtRefreshTokenRepository->findOneBy(['refreshToken' => $refreshToken]);

        if ($jwtRefreshToken) {
            $this->jwtRefreshTokenRepository->remove($jwtRefreshToken);
        }

    }

}
