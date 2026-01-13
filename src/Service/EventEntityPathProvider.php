<?php

namespace OHMedia\EventBundle\Service;

use Doctrine\ORM\QueryBuilder;
use OHMedia\EventBundle\Entity\Event;
use OHMedia\EventBundle\Repository\EventRepository;
use OHMedia\PageBundle\Service\PageRawQuery;
use OHMedia\UtilityBundle\Service\AbstractEntityPathProvider;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventEntityPathProvider extends AbstractEntityPathProvider
{
    public function __construct(
        private EventRepository $eventRepository,
        private PageRawQuery $pageRawQuery,
        private UrlGeneratorInterface $urlGenerator,
        #[Autowire('%oh_media_event.page_template%')]
        private ?string $pageTemplate,
    ) {
    }

    public function getEntityClass(): string
    {
        return Event::class;
    }

    public function getGroupLabel(): string
    {
        return 'Events';
    }

    public function getEntityQueryBuilder(?int $selectedEntityId): QueryBuilder
    {
        $qb = $this->eventRepository->getUpcomingQueryBuilderOrdered();

        if ($selectedEntityId) {
            $qb->orWhere('e.id = :id')
                ->setParameter('id', $selectedEntityId);
        }

        return $qb;
    }

    public function getEntityPath(mixed $entity): ?string
    {
        if (!$entity->isPublished()) {
            return null;
        }

        $pagePath = $this->pageRawQuery->getPathWithTemplate($this->pageTemplate);

        if (!$pagePath) {
            return null;
        }

        return $this->urlGenerator->generate('oh_media_page_frontend', [
            'path' => $pagePath.'/'.$entity->getSlug(),
        ]);
    }
}
