<?php

namespace OHMedia\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use OHMedia\EventBundle\Repository\EventRepository;
use OHMedia\SecurityBundle\Entity\Traits\BlameableTrait;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    use BlameableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __toString(): string
    {
        return 'Event #'.$this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
