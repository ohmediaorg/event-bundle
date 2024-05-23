<?php

namespace OHMedia\EventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\EventBundle\Repository\EventRepository;
use OHMedia\FileBundle\Entity\File;
use OHMedia\SecurityBundle\Entity\Traits\BlameableTrait;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    use BlameableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ticket_url = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?File $image = null;

    /**
     * @var Collection<int, EventTime>
     */
    #[ORM\OneToMany(targetEntity: EventTime::class, mappedBy: 'event', orphanRemoval: true)]
    private Collection $times;

    public function __construct()
    {
        $this->times = new ArrayCollection();
    }

    public function __toString(): string
    {
        return 'Event #'.$this->id;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getTicketUrl(): ?string
    {
        return $this->ticket_url;
    }

    public function setTicketUrl(?string $ticket_url): static
    {
        $this->ticket_url = $ticket_url;

        return $this;
    }

    public function getImage(): ?File
    {
        return $this->image;
    }

    public function setImage(?File $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, EventTime>
     */
    public function getTimes(): Collection
    {
        return $this->times;
    }

    public function addTime(EventTime $time): static
    {
        if (!$this->times->contains($time)) {
            $this->times->add($time);
            $time->setEvent($this);
        }

        return $this;
    }

    public function removeTime(EventTime $time): static
    {
        if ($this->times->removeElement($time)) {
            // set the owning side to null (unless already changed)
            if ($time->getEvent() === $this) {
                $time->setEvent(null);
            }
        }

        return $this;
    }
}
