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

    public function __toString(): string
    {
        $timezone = new \DateTimeZone(date_default_timezone_get());

        // NOTE: DateTimeImmutable::setTimeZone returns a new object

        $startsAt = $this->starts_at->setTimezone($timezone);

        $endsAt = $this->ends_at->setTimezone($timezone);

        if ($startsAt->format('Ymd') === $endsAt->format('Ymd')) {
            return sprintf(
                '%s, %s - %s',
                $startsAt->format('D, M j Y'),
                $startsAt->format('g:ia'),
                $endsAt->format('g:ia'),
            );
        }

        return sprintf(
            '%s - %s',
            $startsAt->format('D, M j Y g:ia'),
            $endsAt->format('D, M j Y g:ia'),
        );
    }

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
