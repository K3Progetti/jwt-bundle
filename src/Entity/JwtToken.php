<?php

namespace K3Progetti\JwtBundle\Entity;

use K3Progetti\JwtBundle\Repository\JwtTokenRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: JwtTokenRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'jwt_tokens')]
class JwtToken
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'citext', length: 255)]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Groups(['jwt_token'])]
    private ?string $username;

    #[ORM\Column(type: 'text')]
    #[Groups(['jwt_token'])]
    private string $token;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?DateTimeImmutable $expiredAt;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $device = null;

    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $ipAddress = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getExpiredAt(): ?DateTimeImmutable
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(DateTimeImmutable $expiredAt): static
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

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
