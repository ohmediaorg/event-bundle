<?php

namespace OHMedia\EventBundle\Security\Voter;

use OHMedia\EventBundle\Entity\EventTag;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\AbstractEntityVoter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class EventTagVoter extends AbstractEntityVoter
{
    public const INDEX = 'index';
    public const CREATE = 'create';
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    public function __construct(
        #[Autowire('%oh_media_event.event_tags%')]
        private bool $eventTagsEnabled
    ) {
    }

    protected function getAttributes(): array
    {
        return [
            self::INDEX,
            self::CREATE,
            self::VIEW,
            self::EDIT,
            self::DELETE,
        ];
    }

    protected function getEntityClass(): string
    {
        return EventTag::class;
    }

    protected function canIndex(EventTag $eventTag, User $loggedIn): bool
    {
        return $this->eventTagsEnabled;
    }

    protected function canCreate(EventTag $eventTag, User $loggedIn): bool
    {
        return $this->eventTagsEnabled;
    }

    protected function canView(EventTag $eventTag, User $loggedIn): bool
    {
        return $this->eventTagsEnabled;
    }

    protected function canEdit(EventTag $eventTag, User $loggedIn): bool
    {
        return $this->eventTagsEnabled;
    }

    protected function canDelete(EventTag $eventTag, User $loggedIn): bool
    {
        return $this->eventTagsEnabled;
    }
}
