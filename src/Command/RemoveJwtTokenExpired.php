<?php

namespace K3Progetti\JwtBundle\Command;

use K3Progetti\JwtBundle\Repository\JwtTokenRepository;
use K3Progetti\JwtBundle\Service\JwtService;
use Random\RandomException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(name: 'jwt:remove-jwt-token-expired', description: 'Remove JWT token expired.')]
class RemoveJwtTokenExpired extends Command
{

    private JwtTokenRepository $jwtTokenRepository;
    private JwtService $jwtService;
    private ParameterBagInterface $params;

    public function __construct(
        JwtTokenRepository    $jwtTokenRepository,
        JwtService            $jwtService,
        ParameterBagInterface $params,

    )
    {
        parent::__construct();
        $this->jwtTokenRepository = $jwtTokenRepository;
        $this->jwtService = $jwtService;
        $this->params = $params;
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

        $refreshTokenTtl = $this->params->get('jwt.refresh_token_ttl');

        $tokens = $this->jwtTokenRepository->findJwtTokenExpired($refreshTokenTtl);

        $io->progressStart(count($tokens));
        foreach ($tokens as $token) {
            $io->progressAdvance();

            $this->jwtService->removeToken($token->getToken());

        }
        $io->progressFinish();

        return Command::SUCCESS;
    }


}
