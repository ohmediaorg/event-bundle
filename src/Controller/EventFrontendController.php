<?php

namespace OHMedia\EventBundle\Controller;

use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\EventBundle\Entity\Event;
use OHMedia\EventBundle\Repository\EventRepository;
use OHMedia\FileBundle\Service\FileManager;
use OHMedia\SettingsBundle\Service\Settings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\Routing\Annotation\Route;

class EventFrontendController extends AbstractController
{
    public function __construct(
        private FileManager $fileManager,
        private UrlHelper $urlHelper,
    ) {
    }

    #[Route('/events', name: 'events_frontend', methods: ['GET'])]
    public function listing(
        EventRepository $eventRepository,
        Paginator $paginator,
    ): Response {
        $qb = $eventRepository->getUpcomingQueryBuilderOrdered();
        $qb->andWhere('e.published_at IS NOT NULL');
        $qb->andWhere('e.published_at < CURRENT_TIMESTAMP()');

        return $this->render('@OHMediaEvent/events.html.twig', [
            'pagination' => $paginator->paginate($qb, 12),
        ]);
    }

    #[Route('/events/{slug}', name: 'event_frontend', methods: ['GET'])]
    public function view(Event $event, Settings $settings): Response
    {
        if (!$event->isPublished()) {
            throw $this->createNotFoundException();
        }

        $content = $this->renderView('@OHMediaEvent/event.html.twig', [
            'event' => $event,
        ]);

        $content .= $this->getSchema($event, $settings->get('schema_organization_name'));

        return new Response($content);
    }

    private function getSchema(Event $event, ?string $organizationName): string
    {
        $times = $event->getTimes();

        $startDate = $times->first()->getStartsAt()->format('c');
        $endDate = $times->last()->getEndsAt()->format('c');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'name' => $event->getName(),
            'description' => $event->getSnippet(),
        ];

        if ($location = $event->getLocation()) {
            $schema['location'] = $location;
        }

        if ($ticketUrl = $event->getTicketUrl()) {
            $schema['url'] = $ticketUrl;
        }

        if ($organizationName) {
            $schema['organizer'] = [
                '@type' => 'Organization',
                'name' => $organizationName,
            ];
        }

        $image = $event->getImage();

        if ($image && $image->getPath()) {
            $path = $this->fileManager->getWebPath($image);

            $schema['image'] = $this->urlHelper->getAbsoluteUrl($path);
        }

        return '<script type="application/ld+json">'.json_encode($schema).'</script>';
    }
}
