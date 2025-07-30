<?php

namespace OHMedia\EventBundle\Twig;

use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\EventBundle\Entity\Event;
use OHMedia\EventBundle\Repository\EventRepository;
use OHMedia\EventBundle\Repository\EventTagRepository;
use OHMedia\FileBundle\Service\FileManager;
use OHMedia\MetaBundle\Entity\Meta;
use OHMedia\PageBundle\Event\DynamicPageEvent;
use OHMedia\PageBundle\Service\PageRenderer;
use OHMedia\SettingsBundle\Service\Settings;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

#[AsEventListener(event: DynamicPageEvent::class, method: 'onDynamicPageEvent')]
class EventsExtension extends AbstractExtension
{
    private ?Event $eventEntity = null;

    public function __construct(
        private EventRepository $eventRepository,
        private FileManager $fileManager,
        private PageRenderer $pageRenderer,
        private Paginator $paginator,
        private Settings $settings,
        private UrlHelper $urlHelper,
        private EventTagRepository $eventTagRepository,
        #[Autowire('%oh_media_event.event_tags%')]
        private bool $eventTagsEnabled,
        private RequestStack $requestStack,
        #[Autowire('%oh_media_event.page_template%')]
        private ?string $pageTemplate,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('events', [$this, 'events'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function onDynamicPageEvent(DynamicPageEvent $dynamicPageEvent)
    {
        $pageRevision = $this->pageRenderer->getCurrentPageRevision();

        $isTemplate = $pageRevision->isTemplate($this->pageTemplate);

        if (!$isTemplate && !$pageRevision->containsShortcode('events()')) {
            return;
        }

        $dynamicPageEvent->stopPropagation();

        $dynamicPart = $this->pageRenderer->getDynamicPart();

        $qb = $this->eventRepository->getFrontendQueryBuilder();
        $qb->andWhere('e.slug = :slug');
        $qb->setParameter('slug', $dynamicPart);
        $qb->setMaxResults(1);

        $this->eventEntity = $qb->getQuery()->getOneOrNullResult();

        if (!$this->eventEntity) {
            throw new NotFoundHttpException('Event not found.');
        }

        $meta = new Meta();
        $meta->setTitle($this->eventEntity->getName());
        $meta->setDescription($this->eventEntity->getSnippet());
        $meta->setImage($this->eventEntity->getImage());
        $meta->setAppendBaseTitle(true);

        $this->pageRenderer->setDynamicMeta($meta);

        $pagePath = $this->pageRenderer->getCurrentPage()->getPath();

        $this->pageRenderer->addDynamicBreadcrumb(
            $this->eventEntity->getName(),
            $pagePath.'/'.$dynamicPart
        );
    }

    public function events(Environment $twig): string
    {
        $pagePath = $this->pageRenderer->getCurrentPage()->getPath();

        if ($this->eventEntity) {
            $content = $twig->render('@OHMediaEvent/event_item.html.twig', [
                'event' => $this->eventEntity,
                'events_page_path' => $pagePath,
            ]);

            $content .= $this->getSchema($this->eventEntity);

            return $content;
        }

        $qb = $this->eventRepository->getFrontendQueryBuilder();

        $tags = null;
        $request = $this->requestStack->getCurrentRequest();
        $query = $request->query->all();
        $activeTags = [];

        if ($this->eventTagsEnabled) {
            // accommodates multiple tags (ie. `tags[]=abc&tags[]=123`)
            $activeTags = isset($query['tags']) && is_array($query['tags']) ?
                $query['tags'] :
                [];

            if ($activeTags) {
                $qb->innerJoin('a.tags', 't');
                $qb->andWhere('t.slug IN (:tags)');
                $qb->setParameter('tags', $activeTags);
            }
        }

        return $twig->render('@OHMediaEvent/event_listing.html.twig', [
            'pagination' => $this->paginator->paginate($qb, 12),
            'events_page_path' => $pagePath,
            'tags' => $this->getTagsArray($query, $activeTags, $pagePath),
        ]);
    }

    private function getTagsArray(
        array $query,
        array $activeTags,
        string $pagePath
    ): array {
        if (!$this->eventTagsEnabled) {
            return [];
        }

        $pageHref = $this->urlGenerator->generate(
            'oh_media_page_frontend',
            ['path' => $pagePath],
        );

        $tagsArray = [];

        $tags = $this->eventTagRepository->createQueryBuilder('at')
            ->select('at')
            ->innerJoin('at.events', 'a')
            ->where('a.published_at IS NOT NULL')
            ->andWhere('a.published_at <= :now')
            ->setParameter('now', DateTimeUtil::getDateTimeUtc())
            ->getQuery()
            ->getResult();

        foreach ($tags as $tag) {
            $slug = $tag->getSlug();

            // making copies for modification
            $thisQuery = $query;
            $thisQueryTags = $activeTags;

            $key = array_search($slug, $thisQueryTags);
            $isActive = false !== $key;

            // building the href for the tag link such that:
            // a) clicking an active tag will make it not active on next page load
            // b) clicking a non-active tag will make it active on next page load

            if ($isActive) {
                array_splice($thisQueryTags, $key, 1);
            } else {
                $thisQueryTags[] = $slug;
            }

            unset($thisQuery['tags']);
            $queryString = http_build_query($thisQuery);
            $tagQueryString = [];

            foreach ($thisQueryTags as $slug) {
                $tagQueryString[] = 'tags[]='.urlencode($slug);
            }

            $tagQueryString = implode('&', $tagQueryString);

            if ($queryString) {
                $queryString = $queryString.'&'.$tagQueryString;
            } else {
                $queryString = $tagQueryString;
            }

            $href = $pageHref;

            if ($queryString) {
                $href .= '?'.$queryString;
            }

            $tagsArray[] = [
                'href' => $href,
                'name' => $tag->getName(),
                'active' => $isActive,
            ];
        }

        if ($tagsArray) {
            array_unshift($tagsArray, [
                'href' => $pageHref,
                'name' => 'All',
                'active' => empty($activeTags),
            ]);
        }

        return $tagsArray;
    }

    private function getSchema(Event $event): string
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

        $organizationName = $this->settings->get('schema_organization_name');

        if ($organizationName) {
            $schema['organizer'] = [
                '@type' => 'Organization',
                'name' => $organizationName,
            ];
        }

        $image = $event->getImage();

        if ($image) {
            $path = $this->fileManager->getWebPath($image);

            $schema['image'] = $this->urlHelper->getAbsoluteUrl($path);
        }

        return '<script type="application/ld+json">'.json_encode($schema).'</script>';
    }
}
