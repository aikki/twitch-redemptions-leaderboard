<?php

namespace App\Entity;

use App\Repository\LeaderboardRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LeaderboardRepository::class)]
class Leaderboard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 127)]
    #[Groups(['bridge'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['bridge'])]
    private ?int $count = null;

    #[ORM\ManyToOne(inversedBy: 'leaderboards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Streamer $streamer = null;

    #[ORM\Column(length: 63)]
    #[Groups(['bridge'])]
    private ?string $UserId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(int $count): static
    {
        $this->count = $count;

        return $this;
    }

    public function getStreamer(): ?Streamer
    {
        return $this->streamer;
    }

    public function setStreamer(?Streamer $streamer): static
    {
        $this->streamer = $streamer;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->UserId;
    }

    public function setUserId(string $UserId): static
    {
        $this->UserId = $UserId;

        return $this;
    }
}
