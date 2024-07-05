<?php

namespace OHMedia\EventBundle\Twig;

use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\EventBundle\Entity\Event;
use OHMedia\EventBundle\Repository\EventRepository;
use OHMedia\FileBundle\Service\FileManager;
use OHMedia\MetaBundle\Entity\Meta;
use OHMedia\PageBundle\Service\PageRenderer;
use OHMedia\SettingsBundle\Service\Settings;
use OHMedia\WysiwygBundle\Twig\AbstractWysiwygExtension;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Twig\TwigFunction;

class WysiwygExtension extends AbstractWysiwygExtension
{
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

    public function events(Environment $twig): string
    {
        $qb = $this->eventRepository->getFrontendQueryBuilder();

        $pagePath = $this->pageRenderer->getCurrentPage()->getPath();

        $dynamicPart = $this->pageRenderer->getDynamicPart();

        if ($dynamicPart) {
            $qb->andWhere('e.slug = :slug');
            $qb->setParameter('slug', $dynamicPart);
            $qb->setMaxResults(1);

            $event = $qb->getQuery()->getOneOrNullResult();

            if ($event) {
                $meta = new Meta();
                $meta->setTitle($event->getName());
                $meta->setDescription($event->getSnippet());
                $meta->setImage($event->getImage());
                $meta->setAppendBaseTitle(true);

                $this->pageRenderer->setMetaEntity($meta);

                $content = $twig->render('@OHMediaEvent/event_item.html.twig', [
                    'event' => $event,
                    'page_path' => $pagePath,
                ]);

                $content .= $this->getSchema($event);

                return $content;
            }

            throw new NotFoundHttpException('Event not found.');
        }

        return $twig->render('@OHMediaEvent/event_listing.html.twig', [
            'pagination' => $this->paginator->paginate($qb, 12),
            'page_path' => $pagePath,
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
