<?php

namespace OHMedia\NewsBundle\Controller;

use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\NewsBundle\Entity\Event;
use OHMedia\NewsBundle\Entity\EventTag;
use OHMedia\NewsBundle\Form\EventTagType;
use OHMedia\NewsBundle\Repository\EventTagRepository;
use OHMedia\NewsBundle\Security\Voter\EventTagVoter;
use OHMedia\UtilityBundle\Form\DeleteType;
use OHMedia\UtilityBundle\Service\EntitySlugger;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Admin]
class EventTagBackendController extends AbstractController
{
    public function __construct(
        private EntitySlugger $entitySlugger,
    ) {
    }

    #[Route('/events/tags', name: 'event_tag_index', methods: ['GET'])]
    public function index(
        EventTagRepository $eventTagRepository,
        Paginator $paginator
    ): Response {
        $newEventTag = new EventTag();

        $this->denyAccessUnlessGranted(
            EventTagVoter::INDEX,
            $newEventTag,
            'You cannot access the list of event tags.'
        );

        $qb = $eventTagRepository->createQueryBuilder('at')
            ->select('at')
            ->addSelect('(
                SELECT COUNT(a.id)
                FROM '.Event::class.' a
                JOIN a.tags t
                WHERE t.id = at.id
            ) AS event_count')
        ;

        $qb->orderBy('at.id', 'desc');

        return $this->render('@OHMediaNews/backend/event_tag/event_tag_index.html.twig', [
            'pagination' => $paginator->paginate($qb, 20),
            'new_event_tag' => $newEventTag,
            'attributes' => $this->getAttributes(),
        ]);
    }

    #[Route('/events/tag/create', name: 'event_tag_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        EventTagRepository $eventTagRepository
    ): Response {
        $eventTag = new EventTag();

        $this->denyAccessUnlessGranted(
            EventTagVoter::CREATE,
            $eventTag,
            'You cannot create a new event tag.'
        );

        $form = $this->createForm(EventTagType::class, $eventTag);

        $form->add('save', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->setSlug($eventTag);

            if ($form->isValid()) {
                $eventTagRepository->save($eventTag, true);

                $this->addFlash('notice', 'The event tag was created successfully.');

                return $this->redirectToRoute('event_tag_index');
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaNews/backend/event_tag/event_tag_create.html.twig', [
            'form' => $form->createView(),
            'event_tag' => $eventTag,
        ]);
    }

    #[Route('/events/tag/{id}/edit', name: 'event_tag_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity(id: 'id')] EventTag $eventTag,
        EventTagRepository $eventTagRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            EventTagVoter::EDIT,
            $eventTag,
            'You cannot edit this event tag.'
        );

        $form = $this->createForm(EventTagType::class, $eventTag);

        $form->add('save', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->setSlug($eventTag);

            if ($form->isValid()) {
                $eventTagRepository->save($eventTag, true);

                $this->addFlash('notice', 'The event tag was updated successfully.');

                return $this->redirectToRoute('event_tag_index', [
                    'id' => $eventTag->getId(),
                ]);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaNews/backend/event_tag/event_tag_edit.html.twig', [
            'form' => $form->createView(),
            'event_tag' => $eventTag,
        ]);
    }

    #[Route('/events/tag/{id}/delete', name: 'event_tag_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[MapEntity(id: 'id')] EventTag $eventTag,
        EventTagRepository $eventTagRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            EventTagVoter::DELETE,
            $eventTag,
            'You cannot delete this event tag.'
        );

        $form = $this->createForm(DeleteType::class, null);

        $form->add('delete', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $eventTagRepository->remove($eventTag, true);

                $this->addFlash('notice', 'The event tag was deleted successfully.');

                return $this->redirectToRoute('event_tag_index');
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaNews/backend/event_tag/event_tag_delete.html.twig', [
            'form' => $form->createView(),
            'event_tag' => $eventTag,
        ]);
    }

    private function setSlug(EventTag $eventTag): void
    {
        $this->entitySlugger->setSlug($eventTag, $eventTag->getName());
    }

    private function getAttributes(): array
    {
        return [
            'view' => EventTagVoter::VIEW,
            'create' => EventTagVoter::CREATE,
            'delete' => EventTagVoter::DELETE,
            'edit' => EventTagVoter::EDIT,
        ];
    }
}
