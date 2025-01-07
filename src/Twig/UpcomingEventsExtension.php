<?php

namespace OHMedia\EventBundle\Twig;

use OHMedia\EventBundle\Repository\EventRepository;
use OHMedia\PageBundle\Service\PageRawQuery;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UpcomingEventsExtension extends AbstractExtension
{
    public function __construct(
        private EventRepository $eventRepository,
        private PageRawQuery $pageRawQuery,
        #[Autowire('%oh_media_event.page_template%')]
        private ?string $pageTemplate,
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

        $pagePath = $this->pageRawQuery->getPathWithShortcodeOrTemplate(
            'events()',
            $this->pageTemplate,
        );

        return $twig->render('@OHMediaEvent/upcoming_events.html.twig', [
            'events' => $events,
            'events_page_path' => $pagePath,
        ]);
    }
}
