<?php

namespace K3Progetti\JwtBundle\Command;

use K3Progetti\JwtBundle\Repository\JwtRefreshTokenRepository;
use K3Progetti\JwtBundle\Service\JwtRefreshService;
use Random\RandomException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(name: 'jwt:remove-jwt-refresh-token-expired', description: 'Remove JWT refresh token expired.')]
class RemoveJwtRefreshTokenExpired extends Command
{

    private JwtRefreshTokenRepository $jwtRefreshTokenRepository;
    private JwtRefreshService $jwtRefreshService;

    public function __construct(
        JwtRefreshTokenRepository $jwtRefreshTokenRepository,
        JwtRefreshService         $jwtRefreshService,

    )
    {
        parent::__construct();
        $this->jwtRefreshTokenRepository = $jwtRefreshTokenRepository;
        $this->jwtRefreshService = $jwtRefreshService;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RandomException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Elimino jwt refresh token expired');

        $refreshTokens = $this->jwtRefreshTokenRepository->findJwtRefreshTokenExpired();

        $io->progressStart(count($refreshTokens));
        foreach ($refreshTokens as $refreshToken) {
            $io->progressAdvance();

            $this->jwtRefreshService->deleteRefreshToken($refreshToken->getRefreshToken());
        }
        $io->progressFinish();

        return Command::SUCCESS;
    }


}
