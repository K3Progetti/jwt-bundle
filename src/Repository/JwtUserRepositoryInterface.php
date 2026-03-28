<?php

namespace K3Progetti\JwtBundle\Repository;

use K3Progetti\JwtBundle\Security\JwtUserInterface;

interface JwtUserRepositoryInterface
{
    public function findOneBy(array $criteria, ?array $orderBy = null): ?JwtUserInterface;

    public function save(JwtUserInterface $user): void;
}