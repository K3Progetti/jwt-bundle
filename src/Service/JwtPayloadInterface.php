<?php

namespace K3Progetti\JwtBundle\Service;

use K3Progetti\JwtBundle\Security\JwtUserInterface;

interface JwtPayloadInterface
{
    public function onBeforePayload(JwtUserInterface $user): array;

    public function onAfterPayload(array $payload, JwtUserInterface $user): array;

    public function overridePayload(JwtUserInterface $user): ?array;
}