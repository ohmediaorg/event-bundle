<?php

namespace OHMedia\EventBundle\Service;

use OHMedia\EventBundle\Repository\EventRepository;
use OHMedia\PageBundle\Service\PageRawQuery;
use OHMedia\PageBundle\Sitemap\AbstractSitemapUrlProvider;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class EventSitemapUrlProvider extends AbstractSitemapUrlProvider
{
    public function __construct(
        private EventRepository $eventRepository,
        private PageRawQuery $pageRawQuery,
        #[Autowire('%oh_media_event.page_template%')]
        private ?string $pageTemplate,
    ) {
    }

    protected function buildSitemapUrls(): void
    {
        $pagePath = $this->pageRawQuery->getPathWithShortcodeOrTemplate(
            'events()',
            $this->pageTemplate,
        );

        if (!$pagePath) {
            return;
        }

        $events = $this->eventRepository->getFrontendQueryBuilder()
            ->getQuery()
            ->getResult();

        foreach ($events as $event) {
            $this->addSitemapUrl(
                $pagePath.'/'.$event->getSlug(),
                $event->getUpdatedAt()
            );
        }
    }
}
