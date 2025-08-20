<?php

namespace OHMedia\EventBundle\Service;

use OHMedia\EventBundle\Entity\Event;
use OHMedia\EventBundle\Entity\EventTag;
use OHMedia\SecurityBundle\Service\EntityChoiceInterface;

class EventEntityChoice implements EntityChoiceInterface
{
    public function getLabel(): string
    {
        return 'Events';
    }

    public function getEntities(): array
    {
        return [
            Event::class,
            EventTag::class,
        ];
    }
}
