<?php

namespace OHMedia\EventBundle\Service;

use OHMedia\BackendBundle\Service\AbstractNavItemProvider;
use OHMedia\BootstrapBundle\Component\Nav\NavItemInterface;
use OHMedia\BootstrapBundle\Component\Nav\NavLink;
use OHMedia\EventBundle\Entity\Event;
use OHMedia\EventBundle\Security\Voter\EventVoter;

class EventNavItemProvider extends AbstractNavItemProvider
{
    public function getNavItem(): ?NavItemInterface
    {
        if ($this->isGranted(EventVoter::INDEX, new Event())) {
            return (new NavLink('Events', 'event_index'))
                ->setIcon('calendar-event-fill');
        }

        return null;
    }
}
