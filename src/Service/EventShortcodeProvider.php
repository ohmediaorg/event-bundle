<?php

namespace OHMedia\EventBundle\Service;

use OHMedia\WysiwygBundle\Shortcodes\AbstractShortcodeProvider;
use OHMedia\WysiwygBundle\Shortcodes\Shortcode;

class EventShortcodeProvider extends AbstractShortcodeProvider
{
    public function getTitle(): string
    {
        return 'Events';
    }

    public function buildShortcodes(): void
    {
        $this->addShortcode(new Shortcode('Event Listing', 'events()', true));
    }
}
