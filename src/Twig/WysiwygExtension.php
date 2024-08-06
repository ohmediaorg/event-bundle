<?php

namespace OHMedia\EventBundle\Twig;

use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\EventBundle\Entity\Event;
use OHMedia\EventBundle\Repository\EventRepository;
use OHMedia\FileBundle\Service\FileManager;
use OHMedia\MetaBundle\Entity\Meta;
use OHMedia\PageBundle\Event\DynamicPageEvent;
use OHMedia\PageBundle\Service\PageRenderer;
use OHMedia\SettingsBundle\Service\Settings;
use OHMedia\WysiwygBundle\Twig\AbstractWysiwygExtension;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Twig\TwigFunction;

#[AsEventListener(event: DynamicPageEvent::class, method: 'onDynamicPageEvent')]
class WysiwygExtension extends AbstractWysiwygExtension
{
    private bool $rendered = false;
    private ?Event $eventEntity = null;

    public function __construct(
        private EventRepository $eventRepository,
        private FileManager $fileManager,
        private PageRenderer $pageRenderer,
        private Paginator $paginator,
        private Settings $settings,
        private UrlHelper $urlHelper,
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

        if ($pageRevision->containsShortcode('{{events()}}')) {
            $dynamicPageEvent->stopPropagation();

            $dynamicPart = $this->pageRenderer->getDynamicPart();

            $qb = $this->eventRepository->getFrontendQueryBuilder();
            $qb->andWhere('e.slug = :slug');
            $qb->setParameter('slug', $dynamicPart);
            $qb->setMaxResults(1);

            $this->eventEntity = $qb->getQuery()->getOneOrNullResult();

            if ($this->eventEntity) {
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
            } else {
                throw new NotFoundHttpException('Event not found.');
            }
        }
    }

    public function events(Environment $twig): string
    {
        if ($this->rendered) {
            return '';
        }

        $this->rendered = true;

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

        return $twig->render('@OHMediaEvent/event_listing.html.twig', [
            'pagination' => $this->paginator->paginate($qb, 12),
            'events_page_path' => $pagePath,
        ]);
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
