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
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

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

        if ($form->isSubmitted()) {
            $this->validateTimes($form);

            $this->setSlug($eventRepository, $event);

            if ($form->isValid()) {
                $this->save($eventRepository, $event);

                $this->addFlash('notice', 'The event was created successfully.');

                return $this->redirectToRoute('event_index');
            }
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

        if ($form->isSubmitted()) {
            $this->validateTimes($form);

            $this->setSlug($eventRepository, $event);

            if ($form->isValid()) {
                $this->save($eventRepository, $event);

                $this->addFlash('notice', 'The event was updated successfully.');

                return $this->redirectToRoute('event_index');
            }
        }

        return $this->render('@OHMediaEvent/event/event_edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    private function validateTimes(FormInterface $form): void
    {
        $formTimes = $form->get('times')->all();

        usort($formTimes, function ($a, $b) {
            return $a->getData()->getStartsAt() <=> $b->getData()->getStartsAt();
        });

        $previousEndsAt = null;
        $overlapMessage = 'The times should not overlap.';
        $startBeforeEndMessage = 'A Start time should be before its corresponding End time.';

        foreach ($formTimes as $formTime) {
            $time = $formTime->getData();

            if ($previousEndsAt && $time->getStartsAt() < $previousEndsAt) {
                $this->addFlash('error', $overlapMessage);

                $form->addError(new FormError($overlapMessage));

                break;
            }

            if ($time->getStartsAt() > $time->getEndsAt()) {
                $this->addFlash('error', $startBeforeEndMessage);

                $form->addError(new FormError($startBeforeEndMessage));

                break;
            }

            $previousEndsAt = $time->getEndsAt();
        }
    }

    private function setSlug(EventRepository $eventRepository, Event $event): void
    {
        $slugger = new AsciiSlugger();

        $slug = $event->getSlug();

        if (!$slug) {
            // create a unique slug
            $name = strtolower($event->getName());

            $slug = $slugger->slug($name);

            $id = $event->getId();

            $i = 1;
            while ($eventRepository->countBySlug($slug, $id)) {
                $slug = $slugger->slug($name.'-'.$i);

                ++$i;
            }
        } else {
            // make sure the slug is formatted properly
            $slug = $slugger->slug(strtolower($slug));
        }

        $event->setSlug($slug);
    }

    private function save(EventRepository $eventRepository, Event $event): void
    {
        foreach ($event->getTimes() as $time) {
            $time->setEvent($event);
        }

        $eventRepository->save($event, true);
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
