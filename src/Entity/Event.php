<?php

namespace OHMedia\EventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Proxy;
use OHMedia\EventBundle\Repository\EventRepository;
use OHMedia\FileBundle\Entity\File;
use OHMedia\UtilityBundle\Entity\BlameableEntityTrait;
use OHMedia\UtilityBundle\Entity\SluggableEntityInterface;
use OHMedia\UtilityBundle\Entity\SluggableEntityTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[UniqueEntity('slug')]
class Event implements SluggableEntityInterface
{
    use BlameableEntityTrait;
    use SluggableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $snippet = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $location = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Length(max: 180)]
    private ?string $ticket_url = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[Assert\Valid]
    private ?File $image = null;

    #[ORM\Column(length: 64)]
    #[Assert\NotBlank]
    #[Assert\Timezone]
    private ?string $timezone = null;

    /**
     * @var Collection<int, EventTime>
     */
    #[ORM\OneToMany(targetEntity: EventTime::class, mappedBy: 'event', orphanRemoval: true, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['starts_at' => 'ASC'])]
    #[Assert\Valid]
    #[Assert\Count(min: 1, minMessage: 'You must have at least one time.')]
    private Collection $times;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $published_at = null;

    /**
     * @var Collection<int, EventTag>
     */
    #[ORM\ManyToMany(targetEntity: EventTag::class, inversedBy: 'events')]
    private Collection $tags;

    public function __construct()
    {
        $this->times = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function __clone()
    {
        if ($this->id) {
            if ($this instanceof Proxy && !$this->__isInitialized()) {
                // Initialize the proxy to load all properties
                $this->__load();
            }

            $this->id = null;
            $this->slug = null;
            $this->published_at = null;
            $this->times = new ArrayCollection();

            if ($this->image) {
                $this->image = clone $this->image;
            }
        }
    }

    public function __toString(): string
    {
        return (string) $this->name;
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

    public function getSnippet(): ?string
    {
        return $this->snippet;
    }

    public function setSnippet(string $snippet): static
    {
        $this->snippet = $snippet;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
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

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): static
    {
        $this->timezone = $timezone;

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

    public function isPast(): bool
    {
        return $this->times->count() && $this->times->last()->isPast();
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->published_at;
    }

    public function setPublishedAt(?\DateTimeImmutable $published_at): static
    {
        $this->published_at = $published_at;

        return $this;
    }

    public function isPublished(): bool
    {
        if (!$this->published_at) {
            return false;
        }

        $utc = new \DateTimeZone('UTC');

        $now = new \DateTime('now', $utc);

        return $this->published_at->setTimezone($utc) < $now;
    }

    /**
     * @return Collection<int, EventTag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    // Used in a listener when tags are disabled
    public function clearTags(): static
    {
        $this->tags = new ArrayCollection();

        return $this;
    }

    public function addTag(EventTag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(EventTag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }
}
