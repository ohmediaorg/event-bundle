<?php

namespace OHMedia\EventBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Events;
use OHMedia\EventBundle\Entity\Event;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsEntityListener(event: Events::postLoad, method: 'postLoad', entity: Event::class)]
class EventListener
{
    public function __construct(
        #[Autowire('%oh_media_event.event_tags%')]
        private bool $eventTagsEnabled,
    ) {
    }

    public function postLoad(Event $event, PostLoadEventArgs $eventArgs)
    {
        if (!$this->eventTagsEnabled) {
            $event->clearTags();
        }
    }
}
