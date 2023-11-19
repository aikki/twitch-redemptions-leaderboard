<?php

namespace App\Entity\Wheel;

use App\Repository\Wheel\IgnoredRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IgnoredRepository::class)]
class Ignored
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $broadcasterId = null;

    #[ORM\Column(length: 255)]
    private ?string $channel = null;

    public function __construct(int $broadcasterId, string $channel)
    {
        $this->broadcasterId = $broadcasterId;
        $this->channel = $channel;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBroadcasterId(): ?int
    {
        return $this->broadcasterId;
    }

    public function setBroadcasterId(int $broadcasterId): static
    {
        $this->broadcasterId = $broadcasterId;

        return $this;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): static
    {
        $this->channel = $channel;

        return $this;
    }
}
