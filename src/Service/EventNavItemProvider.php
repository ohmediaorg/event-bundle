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
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EventNavItemProvider extends AbstractNavItemProvider
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
        #[Autowire('%oh_media_event.event_tags%')]
        private bool $eventTagsEnabled,
    ) {
        parent::__construct($authorizationChecker);
    }

    public function getNavItem(): ?NavItemInterface
    {
        if (!$this->eventTagsEnabled) {
            if ($this->isGranted(EventVoter::INDEX, new Event())) {
                $events = new NavLink('Events', 'event_index');
                $events->setIcon('calendar-event');

                return $events;
            } else {
                return null;
            }
        }

        $nav = (new NavDropdown('Events'))
            ->setIcon('calendar-event');

        if ($this->isGranted(EventVoter::INDEX, new Event())) {
            $events = new NavLink('Events', 'event_index');
            $events->setIcon('calendar-event');

            $nav->addLink($events);
        }

        if ($this->isGranted(EventTagVoter::INDEX, new EventTag())) {
            $tags = new NavLink('Tags', 'event_tag_index');
            $tags->setIcon('tags');

            $nav->addLink($tags);
        }

        return $nav;
    }
}
