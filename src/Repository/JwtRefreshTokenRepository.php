<?php

namespace K3Progetti\JwtBundle\Repository;

use K3Progetti\JwtBundle\Entity\JwtRefreshToken;
use App\Repository\Repository;
use Carbon\Carbon;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends Repository<JwtRefreshToken>
 *
 * @method JwtRefreshToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method JwtRefreshToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method JwtRefreshToken[]    findAll()
 * @method JwtRefreshToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method JwtRefreshToken    getOneById(int $id)
 */
class JwtRefreshTokenRepository extends Repository
{


    public function __construct(
        ManagerRegistry $registry,
    )
    {
        parent::__construct($registry, JwtRefreshToken::class);
    }

    /**
     * @param string $refreshToken
     * @return JwtRefreshToken|null
     */
    public function isValidRefreshToken(string $refreshToken): ?JwtRefreshToken
    {
        $refreshTokenEntity = $this->findOneBy(['refreshToken' => $refreshToken]);

        if (!$refreshTokenEntity || $refreshTokenEntity->getExpiresAt() < Carbon::now()->toDateTimeImmutable()) {
            return null; // Il token non è valido o è scaduto
        }

        return $refreshTokenEntity;
    }


    /**
     * Cerca i refresh token scaduti
     * @return mixed
     */
    public function findJwtRefreshTokenExpired(): mixed
    {
        return $this->createQueryBuilder('refresh_token')
            ->where('refresh_token.expiresAt < :date')
            ->setParameter('date', Carbon::now()->toDateTimeImmutable())
            ->getQuery()
            ->execute();
    }

}
