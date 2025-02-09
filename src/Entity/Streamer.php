<?php

namespace App\Entity;

use App\Entity\Lurkbait\Entry;
use App\Repository\StreamerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StreamerRepository::class)]
class Streamer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 127)]
    private ?string $name = null;

    #[ORM\Column(name: 'request_key', length: 63)]
    private ?string $key = null;

    #[ORM\OneToMany(mappedBy: 'streamer', targetEntity: Leaderboard::class)]
    private Collection $leaderboards;

    #[ORM\OneToMany(mappedBy: 'streamer', targetEntity: Entry::class)]
    private Collection $lurkbaitEntries;

    #[ORM\Column(length: 63)]
    private ?string $viewKey = null;

    public function __construct()
    {
        $this->leaderboards = new ArrayCollection();
    }

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

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(string $key): static
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return Collection<int, Leaderboard>
     */
    public function getLeaderboards(): Collection
    {
        return $this->leaderboards;
    }

    public function addLeaderboard(Leaderboard $leaderboard): static
    {
        if (!$this->leaderboards->contains($leaderboard)) {
            $this->leaderboards->add($leaderboard);
            $leaderboard->setStreamer($this);
        }

        return $this;
    }

    public function removeLeaderboard(Leaderboard $leaderboard): static
    {
        if ($this->leaderboards->removeElement($leaderboard)) {
            // set the owning side to null (unless already changed)
            if ($leaderboard->getStreamer() === $this) {
                $leaderboard->setStreamer(null);
            }
        }

        return $this;
    }

    public function getViewKey(): ?string
    {
        return $this->viewKey;
    }

    public function setViewKey(string $viewKey): static
    {
        $this->viewKey = $viewKey;

        return $this;
    }

    /**
     * @return Collection<int, Leaderboard>
     */
    public function getLurkbaitEntries(): Collection
    {
        return $this->lurkbaitEntries->filter(function (Entry $entry) { return $entry->isActive(); });
    }

    public function addLurkbaitEntries(Entry $entry): static
    {
        if (!$this->lurkbaitEntries->contains($entry)) {
            $this->lurkbaitEntries->add($entry);
            $entry->setStreamer($this);
        }

        return $this;
    }

    public function removeLurkbaitEntries(Entry $entry): static
    {
        if ($this->lurkbaitEntries->removeElement($entry)) {
            // set the owning side to null (unless already changed)
            if ($entry->getStreamer() === $this) {
                $entry->setStreamer(null);
            }
        }

        return $this;
    }
}
