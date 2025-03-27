<?php

namespace K3Progetti\JwtBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class JwtUserLoggedOutEvent extends Event
{
    public function __construct(
        public readonly int    $userId,
        public readonly string $username,
    )
    {
    }
}
