<?php

namespace K3Progetti\JwtBundle\Service;

use App\Entity\User;

interface JwtPayloadModifierInterface
{
    public function modify(array $payload, User $user): array;
}
