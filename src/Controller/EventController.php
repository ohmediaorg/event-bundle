<?php

namespace OHMedia\EventBundle\Controller;

use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\EventBundle\Entity\Event;
use OHMedia\EventBundle\Form\EventType;
use OHMedia\EventBundle\Repository\EventRepository;
use OHMedia\EventBundle\Security\Voter\EventVoter;
use OHMedia\SecurityBundle\Form\DeleteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Admin]
class EventController extends AbstractController
{
    #[Route('/events', name: 'event_index', methods: ['GET'])]
    public function index(
        EventRepository $eventRepository,
        Paginator $paginator
    ): Response {
        $newEvent = new Event();

        $this->denyAccessUnlessGranted(
            EventVoter::INDEX,
            $newEvent,
            'You cannot access the list of events.'
        );

        // TODO: listings for upcoming vs past events
        // order upcoming by starts_at ASC
        // order past by starts_at DESC

        $qb = $eventRepository->createQueryBuilder('e');
        $qb->orderBy('e.id', 'desc');

        return $this->render('@OHMediaEvent/event/event_index.html.twig', [
            'pagination' => $paginator->paginate($qb, 20),
            'new_event' => $newEvent,
            'attributes' => $this->getAttributes(),
        ]);
    }

    #[Route('/event/create', name: 'event_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        EventRepository $eventRepository
    ): Response {
        $event = new Event();

        $this->denyAccessUnlessGranted(
            EventVoter::CREATE,
            $event,
            'You cannot create a new event.'
        );

        $form = $this->createForm(EventType::class, $event);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO: generate unique slug

            $eventRepository->save($event, true);

            $this->addFlash('notice', 'The event was created successfully.');

            return $this->redirectToRoute('event_index');
        }

        return $this->render('@OHMediaEvent/event/event_create.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/event/{id}/edit', name: 'event_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Event $event,
        EventRepository $eventRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            EventVoter::EDIT,
            $event,
            'You cannot edit this event.'
        );

        $form = $this->createForm(EventType::class, $event);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO: generate unique slug

            $eventRepository->save($event, true);

            $this->addFlash('notice', 'The event was updated successfully.');

            return $this->redirectToRoute('event_index');
        }

        return $this->render('@OHMediaEvent/event/event_edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/event/{id}/delete', name: 'event_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        Event $event,
        EventRepository $eventRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            EventVoter::DELETE,
            $event,
            'You cannot delete this event.'
        );

        $form = $this->createForm(DeleteType::class, null);

        $form->add('delete', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventRepository->remove($event, true);

            $this->addFlash('notice', 'The event was deleted successfully.');

            return $this->redirectToRoute('event_index');
        }

        return $this->render('@OHMediaEvent/event/event_delete.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    private function getAttributes(): array
    {
        return [
            'create' => EventVoter::CREATE,
            'delete' => EventVoter::DELETE,
            'edit' => EventVoter::EDIT,
        ];
    }
}
