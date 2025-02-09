<?php

namespace App\Entity\Lurkbait;

use App\Entity\Streamer;
use App\Repository\Wheel\WheelRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: WheelRepository::class)]
#[ORM\Table(name: "lurkbait_entry")]
class Entry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lurkbaitEntries', targetEntity: Streamer::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Streamer $streamer = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['bridge'])]
    private string $name;

    #[ORM\Column(nullable: true)]
    #[Groups(['bridge'])]
    private string $displayName;

    #[ORM\Column(nullable: true)]
    #[Groups(['bridge'])]
    private int $gold;

    #[ORM\Column(nullable: true)]
    #[Groups(['bridge'])]
    private int $totalCasts;

    #[ORM\Column]
    private bool $active = false;

    public function __construct(Streamer $streamer, string $name, string $displayName, int $gold, int $totalCasts)
    {
        $this->streamer = $streamer;
        $this->name = $name;
        $this->displayName = $displayName;
        $this->gold = $gold;
        $this->totalCasts = $totalCasts;
        $this->active = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStreamer(): ?Streamer
    {
        return $this->streamer;
    }

    public function setStreamer(?Streamer $streamer): Entry
    {
        $this->streamer = $streamer;
        return $this;
    }


    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): Entry
    {
        $this->displayName = $displayName;
        return $this;
    }

    public function getGold(): int
    {
        return $this->gold;
    }

    public function setGold(int $gold): Entry
    {
        $this->gold = $gold;
        return $this;
    }

    public function getTotalCasts(): int
    {
        return $this->totalCasts;
    }

    public function setTotalCasts(int $totalCasts): Entry
    {
        $this->totalCasts = $totalCasts;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): Entry
    {
        $this->active = $active;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Entry
    {
        $this->name = $name;
        return $this;
    }

}
