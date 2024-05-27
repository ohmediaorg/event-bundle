<?php

namespace OHMedia\EventBundle\Security\Voter;

use OHMedia\EventBundle\Entity\Event;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\AbstractEntityVoter;

class EventVoter extends AbstractEntityVoter
{
    public const INDEX = 'index';
    public const CREATE = 'create';
    public const EDIT = 'edit';
    public const DUPLICATE = 'duplicate';
    public const DELETE = 'delete';

    protected function getAttributes(): array
    {
        return [
            self::INDEX,
            self::CREATE,
            self::EDIT,
            self::DUPLICATE,
            self::DELETE,
        ];
    }

    protected function getEntityClass(): string
    {
        return Event::class;
    }

    protected function canIndex(Event $event, User $loggedIn): bool
    {
        return true;
    }

    protected function canCreate(Event $event, User $loggedIn): bool
    {
        return true;
    }

    protected function canEdit(Event $event, User $loggedIn): bool
    {
        // TODO: don't allow past events to be edited?
        return true;
    }

    protected function canDuplicate(Event $event, User $loggedIn): bool
    {
        return true;
    }

    protected function canDelete(Event $event, User $loggedIn): bool
    {
        return true;
    }
}
