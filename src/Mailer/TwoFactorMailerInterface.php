<?php

namespace K3Progetti\JwtBundle\Mailer;

use K3Progetti\JwtBundle\Security\JwtUserInterface;

interface TwoFactorMailerInterface
{
    public function sendTwoFactorCode(JwtUserInterface $user, string $code): void;
}