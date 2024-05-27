<?php

namespace OHMedia\EventBundle\Controller;

use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\EventBundle\Entity\Event;
use OHMedia\EventBundle\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventFrontendController extends AbstractController
{
    #[Route('/events', name: 'events_frontend', methods: ['GET'])]
    public function listing(
        EventRepository $eventRepository,
        Paginator $paginator,
    ): Response {
        $qb = $eventRepository->getUpcomingQueryBuilderOrdered();
        $qb->andWhere('e.published_at IS NOT NULL');
        $qb->andWhere('e.published_at < CURRENT_TIMESTAMP()');

        return $this->render('@OHMediaEvent/events.html.twig', [
            'pagination' => $paginator->paginate($qb, 20),
        ]);
    }

    #[Route('/events/{slug}', name: 'event_frontend', methods: ['GET'])]
    public function view(Event $event): Response
    {
        if (!$event->isPublished()) {
            throw $this->createNotFoundException();
        }

        return $this->render('@OHMediaEvent/event.html.twig', [
            'event' => $event,
        ]);
    }
}
