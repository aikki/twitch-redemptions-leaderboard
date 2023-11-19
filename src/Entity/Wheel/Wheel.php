<?php

namespace App\Entity\Wheel;

use App\Repository\Wheel\WheelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WheelRepository::class)]
class Wheel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\OneToMany(mappedBy: 'wheel', targetEntity: Entry::class, orphanRemoval: true)]
    private Collection $entries;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Entry $winner = null;

    #[ORM\Column]
    private ?bool $spin = false;

    #[ORM\Column(nullable: true)]
    private ?int $broadcasterId = null;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection<int, Entry>
     */
    public function getEntries(): Collection
    {
        return $this->entries;
    }

    public function addEntry(Entry $entry): static
    {
        if (!$this->entries->contains($entry)) {
            $this->entries->add($entry);
            $entry->setWheel($this);
        }

        return $this;
    }

    public function removeEntry(Entry $entry): static
    {
        if ($this->entries->removeElement($entry)) {
            // set the owning side to null (unless already changed)
            if ($entry->getWheel() === $this) {
                $entry->setWheel(null);
            }
        }

        return $this;
    }

    public function getWinner(): ?Entry
    {
        return $this->winner;
    }

    public function setWinner(?Entry $winner): static
    {
        $this->winner = $winner;

        return $this;
    }

    public function isSpin(): ?bool
    {
        return $this->spin;
    }

    public function setSpin(bool $spin): static
    {
        $this->spin = $spin;

        return $this;
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
}
