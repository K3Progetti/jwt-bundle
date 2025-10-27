<?php

namespace K3Progetti\JwtBundle\Event;

use K3Progetti\MercureBridgeBundle\Enum\NotificationType;
use K3Progetti\MercureBridgeBundle\Service\NotificationMessageFactory;
use K3Progetti\MercureBridgeBundle\Service\SendNotification;
use Symfony\Contracts\EventDispatcher\Event;

class JwtUserLoggedOutEvent extends Event
{
    public function __construct(
        public readonly int      $userId,
        public readonly string   $username,
        private SendNotification $sendNotification
    )
    {
        $data = NotificationMessageFactory::create(
            NotificationType::StatusUpdate,
            'logout',
            ['id' => $userId],
            [
                'entity' => 'jwt',
            ]);
        $this->sendNotification->send($data);

    }
}
