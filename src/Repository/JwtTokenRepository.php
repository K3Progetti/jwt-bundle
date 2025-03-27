<?php

namespace K3Progetti\JwtBundle\Repository;

use K3Progetti\JwtBundle\Entity\JwtToken;
use App\Repository\Repository;
use Carbon\Carbon;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method JwtToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method JwtToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method JwtToken[]    findAll()
 * @method JwtToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method JwtToken    getOneById(int $id)
 */
class JwtTokenRepository extends Repository
{

    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct($registry, JwtToken::class);
    }

    /**
     * Cerca i refresh token scaduti
     * @param int $refreshTokenTtl
     * @return mixed
     */
    public function findJwtTokenExpired(int $refreshTokenTtl = 604800): mixed
    {
        // Ho voluto mettere + 5 giorni per essere sicuro che sia sopra il refresh token.
        // Ho usato apposta il tempo del refresh... perchè il refresh potrebbe ridare il token
        $dateLimit = Carbon::now()->subSeconds($refreshTokenTtl)->addDays(5)->toDateTimeImmutable();

        return $this->createQueryBuilder('jwt_token')
            ->where('jwt_token.expiredAt < :dateLimit')
            ->setParameter('dateLimit', $dateLimit)
            ->getQuery()
            ->getResult();
    }
}
