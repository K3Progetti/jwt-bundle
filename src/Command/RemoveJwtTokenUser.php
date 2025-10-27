<?php

namespace K3Progetti\JwtBundle\Command;

use K3Progetti\JwtBundle\Repository\JwtRefreshTokenRepository;
use K3Progetti\JwtBundle\Repository\JwtTokenRepository;
use App\Repository\UserRepository;
use K3Progetti\MercureBridgeBundle\Enum\NotificationType;
use K3Progetti\MercureBridgeBundle\Service\NotificationMessageFactory;
use K3Progetti\MercureBridgeBundle\Service\SendNotification;
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

    public function __construct(
        private readonly JwtTokenRepository        $jwtTokenRepository,
        private readonly UserRepository            $userRepository,
        private readonly JwtRefreshTokenRepository $jwtRefreshTokenRepository,
        private readonly ParameterBagInterface     $params,
        private readonly EventDispatcherInterface  $eventDispatcher,
        private readonly SendNotification          $sendNotification

    )
    {
        parent::__construct();
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
        $data = NotificationMessageFactory::create(
            NotificationType::StatusUpdate,
            'logout',
            ['id' => $userId],
            [
                'entity' => 'jwt',
            ]);

        $this->sendNotification->send($data);

        return Command::SUCCESS;
    }


}
