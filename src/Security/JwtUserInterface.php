<?php

namespace K3Progetti\JwtBundle\Security;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface JwtUserInterface extends UserInterface, PasswordAuthenticatedUserInterface
{
    public function getId(): mixed;

    public function getUsername(): string;

    public function getName(): string;

    public function getSurname(): string;

    public function isActive(): bool;

    public function isTwoFactorAuth(): bool;

    public function getTwoFactorAuthCode(): ?string;

    public function setTwoFactorAuthCode(?string $code): static;

    public function setTwoFactorAuthCodeExpired(\DateTimeInterface $dt): static;
}