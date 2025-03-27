<?php

namespace K3Progetti\JwtBundle\Entity;

use K3Progetti\JwtBundle\Repository\JwtRefreshTokenRepository;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: JwtRefreshTokenRepository::class)]
#[ORM\Table(name: 'refresh_tokens')]
class JwtRefreshToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'jwtRefreshTokens')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $appUser;

    #[ORM\Column(type: 'string', unique: true)]
    #[Assert\NotBlank]
    private string $refreshToken;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $expiresAt;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $device = null;

    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $ipAddress = null;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): static
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function getDevice(): ?string
    {
        return $this->device;
    }

    public function setDevice(?string $device): static
    {
        $this->device = $device;
        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAppUser(): User
    {
        return $this->appUser;
    }

    public function setAppUser(User $appUser): static
    {
        $this->appUser = $appUser;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }
}

