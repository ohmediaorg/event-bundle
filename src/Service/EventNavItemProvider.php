<?php

namespace OHMedia\EventBundle\Service;

use OHMedia\BackendBundle\Service\AbstractNavItemProvider;
use OHMedia\BootstrapBundle\Component\Nav\NavDropdown;
use OHMedia\BootstrapBundle\Component\Nav\NavItemInterface;
use OHMedia\BootstrapBundle\Component\Nav\NavLink;
use OHMedia\EventBundle\Entity\Event;
use OHMedia\EventBundle\Entity\EventTag;
use OHMedia\EventBundle\Security\Voter\EventTagVoter;
use OHMedia\EventBundle\Security\Voter\EventVoter;

class EventNavItemProvider extends AbstractNavItemProvider
{
    public function getNavItem(): ?NavItemInterface
    {
        $nav = (new NavDropdown('Events'))
            ->setIcon('calendar3');

        if ($this->isGranted(EventVoter::INDEX, new Event())) {
            $events = new NavLink('Events', 'event_index');
            $events->setIcon('calendar3');

            $nav->addLink($events);
        }

        if ($this->isGranted(EventTagVoter::INDEX, new EventTag())) {
            $tags = new NavLink('Tags', 'event_tag_index');
            $tags->setIcon('tag');

            $nav->addLink($tags);
        }

        return $nav;
    }
}
