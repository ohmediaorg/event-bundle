<?php

namespace OHMedia\EventBundle\Twig;

use OHMedia\EventBundle\Repository\EventRepository;
use OHMedia\PageBundle\Service\PageRawQuery;
use OHMedia\WysiwygBundle\Twig\AbstractWysiwygExtension;
use Twig\Environment;
use Twig\TwigFunction;

class UpcomingEventsExtension extends AbstractWysiwygExtension
{
    public function __construct(
        private EventRepository $eventRepository,
        private PageRawQuery $pageRawQuery,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('upcoming_events', [$this, 'upcomingEvents'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function upcomingEvents(Environment $twig, int $limit = 3): string
    {
        if ($limit <= 0) {
            $limit = 3;
        }

        $qb = $this->eventRepository->getFrontendQueryBuilder();
        $qb->setMaxResults($limit);

        $events = $qb->getQuery()->getResult();

        $pagePath = $this->pageRawQuery->getPathWithShortcode('events()');

        return $twig->render('@OHMediaEvent/upcoming_events.html.twig', [
            'events' => $events,
            'page_path' => $pagePath,
        ]);
    }
}
