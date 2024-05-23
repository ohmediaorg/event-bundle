<?php

namespace OHMedia\EventBundle\Entity;

use App\Repository\EventTimeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventTimeRepository::class)]
class EventTime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $starts_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $ends_at = null;

    #[ORM\ManyToOne(inversedBy: 'times')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartsAt(): ?\DateTimeImmutable
    {
        return $this->starts_at;
    }

    public function setStartsAt(\DateTimeImmutable $starts_at): static
    {
        $this->starts_at = $starts_at;

        return $this;
    }

    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->ends_at;
    }

    public function setEndsAt(\DateTimeImmutable $ends_at): static
    {
        $this->ends_at = $ends_at;

        return $this;
    }

    public function yearsMatch(): bool
    {
        return $this->starts_at->format('Y') === $this->ends_at->format('Y');
    }

    public function monthsMatch(): bool
    {
        if (!$this->yearsMatch()) {
            return false;
        }

        return $this->starts_at->format('m') === $this->ends_at->format('m');
    }

    public function daysMatch(): bool
    {
        if (!$this->monthsMatch()) {
            return false;
        }

        return $this->starts_at->format('d') === $this->ends_at->format('d');
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }
}
