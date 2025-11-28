<?php

namespace OHMedia\EventBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use OHMedia\BackendBundle\Form\MultiSaveType;
use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\EventBundle\Entity\Event;
use OHMedia\EventBundle\Entity\EventTag;
use OHMedia\EventBundle\Entity\EventTime;
use OHMedia\EventBundle\Form\EventType;
use OHMedia\EventBundle\Repository\EventRepository;
use OHMedia\EventBundle\Security\Voter\EventTagVoter;
use OHMedia\EventBundle\Security\Voter\EventVoter;
use OHMedia\TimezoneBundle\Util\DateTimeUtil;
use OHMedia\UtilityBundle\Form\DeleteType;
use OHMedia\UtilityBundle\Service\EntitySlugger;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;

#[Admin]
class EventBackendController extends AbstractController
{
    public function __construct(
        private EventRepository $eventRepository,
        private EntitySlugger $entitySlugger,
    ) {
    }

    #[Route('/events', name: 'event_index', methods: ['GET'])]
    public function index(
        Paginator $paginator,
        Request $request,
        #[Autowire('%oh_media_event.event_tags%')]
        bool $eventTagsEnabled,
    ): Response {
        $newEvent = new Event();
        $newEventTag = new EventTag();

        $this->denyAccessUnlessGranted(
            EventVoter::INDEX,
            $newEvent,
            'You cannot access the list of events.'
        );

        $qb = $this->eventRepository->createQueryBuilder('e');

        $searchForm = $this->getSearchForm($request);

        $this->applySearch($searchForm, $qb);

        return $this->render('@OHMediaEvent/event/event_index.html.twig', [
            'pagination' => $paginator->paginate($qb, 20),
            'new_event' => $newEvent,
            'new_event_tag' => $newEventTag,
            'attributes' => $this->getAttributes(),
            'search_form' => $searchForm,
            'event_tags_enabled' => $eventTagsEnabled,
        ]);
    }

    private function getSearchForm(Request $request): FormInterface
    {
        $formBuilder = $this->container->get('form.factory')
            ->createNamedBuilder('', FormType::class, null, [
                'csrf_protection' => false,
            ]);

        $formBuilder->setMethod('GET');

        $formBuilder->add('search', TextType::class, [
            'required' => false,
        ]);

        $formBuilder->add('status', ChoiceType::class, [
            'required' => false,
            'choices' => [
                'All' => '',
                'Published' => 'published',
                'Scheduled' => 'scheduled',
                'Draft' => 'draft',
            ],
        ]);

        $formBuilder->add('type', ChoiceType::class, [
            'required' => false,
            'choices' => [
                'All' => '',
                'Upcoming' => 'upcoming',
                'Past' => 'past',
            ],
            'data' => 'upcoming',
        ]);

        $formBuilder->add('order', ChoiceType::class, [
            'choices' => [
                'Newest to Oldest' => 'desc',
                'Oldest to Newest' => 'asc',
            ],
            'data' => 'asc',
        ]);

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        return $form;
    }

    private function applySearch(FormInterface $form, QueryBuilder $qb): void
    {
        $search = $form->get('search')->getData();

        if ($search) {
            $searchFields = [
                'e.title',
                'e.slug',
                'e.snippet',
                'e.description',
                'e.location',
            ];

            $searchLikes = [];
            foreach ($searchFields as $searchField) {
                $searchLikes[] = "$searchField LIKE :search";
            }

            $qb->andWhere('('.implode(' OR ', $searchLikes).')')
                ->setParameter('search', '%'.$search.'%');
        }

        $status = $form->get('status')->getData();

        if ('published' === $status) {
            $qb->andWhere('e.published_at IS NOT NULL');
            $qb->andWhere('e.published_at <= :now');
            $qb->setParameter('now', DateTimeUtil::getDateTimeUtc());
        } elseif ('scheduled' === $status) {
            $qb->andWhere('e.published_at IS NOT NULL');
            $qb->andWhere('e.published_at > :now');
            $qb->setParameter('now', DateTimeUtil::getDateTimeUtc());
        } elseif ('draft' === $status) {
            $qb->andWhere('e.published_at IS NULL');
        }

        $type = $form->get('type')->getData();

        if ('upcoming' === $type) {
            $qb->andWhere('(
                SELECT MAX(et.ends_at)
                FROM OHMedia\EventBundle\Entity\EventTime et
                WHERE IDENTITY(et.event) = e.id
            ) > :now');
            $qb->setParameter('now', DateTimeUtil::getDateTimeUtc());
        } elseif ('past' === $type) {
            $qb->andWhere('(
                SELECT MAX(et.ends_at)
                FROM OHMedia\EventBundle\Entity\EventTime et
                WHERE IDENTITY(et.event) = e.id
            ) < :now');
            $qb->setParameter('now', DateTimeUtil::getDateTimeUtc());
        }

        $order = $form->get('order')->getData();

        if ('desc' === $order) {
            $qb->addSelect('(
                SELECT MAX(et2.ends_at)
                FROM OHMedia\EventBundle\Entity\EventTime et2
                WHERE IDENTITY(et2.event) = e.id
            ) AS HIDDEN ends_at');
            $qb->orderBy('ends_at', 'DESC');
        } else {
            $qb->addSelect('(
                SELECT MIN(et2.starts_at)
                FROM OHMedia\EventBundle\Entity\EventTime et2
                WHERE IDENTITY(et2.event) = e.id
            ) AS HIDDEN starts_at');
            $qb->orderBy('starts_at', 'ASC');
        }
    }

    #[Route('/event/create', name: 'event_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $event = new Event();

        $this->denyAccessUnlessGranted(
            EventVoter::CREATE,
            $event,
            'You cannot create a new event.'
        );

        $form = $this->createForm(EventType::class, $event);

        $form->add('save', MultiSaveType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->validateTimes($form);

            $this->setSlug($event);

            if ($form->isValid()) {
                $this->save($event, $form, $request);

                $this->addFlash('notice', 'The event was created successfully.');

                return $this->redirectForm($event, $form);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaEvent/event/event_create.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/event/{id}/edit', name: 'event_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity(id: 'id')] Event $event,
    ): Response {
        $this->denyAccessUnlessGranted(
            EventVoter::EDIT,
            $event,
            'You cannot edit this event.'
        );

        $form = $this->createForm(EventType::class, $event);

        $form->add('save', MultiSaveType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->validateTimes($form);

            $this->setSlug($event);

            if ($form->isValid()) {
                $this->save($event, $form, $request);

                $this->addFlash('notice', 'The event was updated successfully.');

                return $this->redirectForm($event, $form);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaEvent/event/event_edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    private function redirectForm(Event $event, FormInterface $form): Response
    {
        $clickedButtonName = $form->getClickedButton()->getName() ?? null;

        if ('keep_editing' === $clickedButtonName) {
            return $this->redirectToRoute('event_edit', [
                'id' => $event->getId(),
            ]);
        } elseif ('add_another' === $clickedButtonName) {
            return $this->redirectToRoute('event_create');
        } else {
            return $this->redirectToRoute('event_index');
        }
    }

    #[Route('/event/{id}/duplicate', name: 'event_duplicate', methods: ['GET', 'POST'])]
    public function duplicate(
        Request $request,
        #[MapEntity(id: 'id')] Event $existingEvent,
    ): Response {
        $this->denyAccessUnlessGranted(
            EventVoter::DUPLICATE,
            $existingEvent,
            'You cannot duplicate this event.'
        );

        $newEvent = clone $existingEvent;

        $formBuilder = $this->createFormBuilder();

        $formBuilder->add('amount', IntegerType::class, [
            'attr' => [
                'min' => 1,
            ],
            'constraints' => [
                new Assert\GreaterThanOrEqual(1),
            ],
            'data' => 1,
        ]);

        $formBuilder->add('unit', ChoiceType::class, [
            'choices' => [
                'days' => 'day',
                'weeks' => 'week',
                'months' => 'month',
                'years' => 'year',
            ],
        ]);

        $formBuilder->add('duplicate', SubmitType::class);

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->setSlug($newEvent);

            if ($form->isValid()) {
                $amount = $form->get('amount')->getData();
                $unit = $form->get('unit')->getData();

                $interval = \DateInterval::createFromDateString("$amount $unit");

                foreach ($existingEvent->getTimes() as $existingTime) {
                    $newTime = new EventTime();

                    $newTime->setStartsAt($existingTime->getStartsAt()->add($interval));
                    $newTime->setEndsAt($existingTime->getEndsAt()->add($interval));
                    $newEvent->addTime($newTime);
                }

                $this->eventRepository->save($newEvent, true);

                $this->addFlash('notice', 'The event was duplicated successfully.');

                return $this->redirectToRoute('event_edit', [
                    'id' => $newEvent->getId(),
                ]);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaEvent/event/event_duplicate.html.twig', [
            'form' => $form->createView(),
            'existing_event' => $existingEvent,
            'new_event' => $newEvent,
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

        foreach ($formTimes as $formTime) {
            $time = $formTime->getData();

            if ($previousEndsAt && $time->getStartsAt() < $previousEndsAt) {
                $formTime->get('starts_at')->addError(new FormError('This value is overlapping the previous end date.'));
            }

            $previousEndsAt = $time->getEndsAt();
        }
    }

    private function setSlug(Event $event): void
    {
        $this->entitySlugger->setSlug($event, $event->getName());
    }

    private function setTimezone(
        Event $event,
        FormInterface $form,
        Request $request
    ): void {
        $times = $form->get('times')->getData();
        $requestData = $request->request->all($form->getName());
        $timesData = $requestData['times'];

        $timezone = new \DateTimeZone($event->getTimezone());

        foreach ($times as $i => $time) {
            $startsAtData = $timesData[$i]['starts_at'];
            $endsAtData = $timesData[$i]['ends_at'];

            $startsAt = new \DateTimeImmutable($startsAtData, $timezone);
            $endsAt = new \DateTimeImmutable($endsAtData, $timezone);

            $time->setStartsAt($startsAt)->setEndsAt($endsAt);
        }
    }

    private function save(
        Event $event,
        FormInterface $form,
        Request $request
    ): void {
        $this->setTimezone($event, $form, $request);

        $this->eventRepository->save($event, true);
    }

    #[Route('/event/{id}/delete', name: 'event_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[MapEntity(id: 'id')] Event $event,
    ): Response {
        $this->denyAccessUnlessGranted(
            EventVoter::DELETE,
            $event,
            'You cannot delete this event.'
        );

        $form = $this->createForm(DeleteType::class, null);

        $form->add('delete', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->eventRepository->remove($event, true);

                $this->addFlash('notice', 'The event was deleted successfully.');

                return $this->redirectToRoute('event_index');
            }

            $this->addFlash('error', 'There are some errors in the form below.');
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
            'duplicate' => EventVoter::DUPLICATE,
            'view_tags' => EventTagVoter::VIEW,
        ];
    }
}
