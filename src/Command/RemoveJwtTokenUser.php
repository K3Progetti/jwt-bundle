<?php

namespace K3Progetti\JwtBundle\Command;

use K3Progetti\JwtBundle\Event\JwtUserLoggedOutEvent;
use K3Progetti\JwtBundle\Repository\JwtRefreshTokenRepository;
use K3Progetti\JwtBundle\Repository\JwtTokenRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCommand(name: 'jwt:remove-jwt-token-user', description: 'Remove JWT user.')]
class RemoveJwtTokenUser extends Command
{

    private JwtTokenRepository $jwtTokenRepository;
    private UserRepository $userRepository;
    private JwtRefreshTokenRepository $jwtRefreshTokenRepository;
    private ParameterBagInterface $params;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        JwtTokenRepository        $jwtTokenRepository,
        UserRepository            $userRepository,
        JwtRefreshTokenRepository $jwtRefreshTokenRepository,
        ParameterBagInterface     $params,
        EventDispatcherInterface  $eventDispatcher

    )
    {
        parent::__construct();
        $this->jwtTokenRepository = $jwtTokenRepository;
        $this->userRepository = $userRepository;
        $this->jwtRefreshTokenRepository = $jwtRefreshTokenRepository;
        $this->params = $params;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('userId', InputArgument::REQUIRED, 'User id');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Elimino jwt refresh token expired');

        $userId = $input->getArgument('userId');


        $user = $this->userRepository->findOneBy(['id' => $userId]);
        $username = $user?->getUsername();

        $jwtTokens = $this->jwtTokenRepository->findBy(['username' => $username]);

        if (count($jwtTokens) > 0) {
            foreach ($jwtTokens as $jwtToken) {
                $this->jwtTokenRepository->remove($jwtToken, false);
            }
            $this->jwtTokenRepository->flush();
        }

        $jwtRefreshTokens = $this->jwtRefreshTokenRepository->findBy(['appUser' => $user->getId()]);

        if (count($jwtRefreshTokens) > 0) {
            foreach ($jwtRefreshTokens as $jwtRefreshToken) {
                $this->jwtRefreshTokenRepository->remove($jwtRefreshToken, false);
            }
            $this->jwtRefreshTokenRepository->flush();
        }

        // Invio un messaggio per comunicare che è stato disattivato
        $this->eventDispatcher->dispatch(new JwtUserLoggedOutEvent($user->getId(), $user->getUsername()));

        return Command::SUCCESS;
    }


}
