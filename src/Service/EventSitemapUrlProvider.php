<?php

namespace OHMedia\EventBundle\Service;

use OHMedia\EventBundle\Repository\EventRepository;
use OHMedia\PageBundle\Service\PageRawQuery;
use OHMedia\PageBundle\Sitemap\AbstractSitemapUrlProvider;

class EventSitemapUrlProvider extends AbstractSitemapUrlProvider
{
    public function __construct(
        private EventRepository $eventRepository,
        private PageRawQuery $pageRawQuery,
    ) {
    }

    protected function buildSitemapUrls(): void
    {
        $pagePath = $this->pageRawQuery->getPathWithShortcode('events()');

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
