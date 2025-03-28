<?php

namespace K3Progetti\JwtBundle\Service;

use App\Entity\User;

interface JwtPayloadInterface
{
    public function onBeforePayload(User $user): array;

    public function onAfterPayload(array $payload, User $user): array;

    public function overridePayload(User $user): ?array;
}