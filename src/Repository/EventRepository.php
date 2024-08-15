<?php

namespace OHMedia\EventBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use OHMedia\EventBundle\Entity\Event;
use OHMedia\TimezoneBundle\Util\DateTimeUtil;
use OHMedia\WysiwygBundle\Repository\WysiwygRepositoryInterface;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository implements WysiwygRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function save(Event $event, bool $flush = false): void
    {
        $this->getEntityManager()->persist($event);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Event $event, bool $flush = false): void
    {
        $this->getEntityManager()->remove($event);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getUpcomingQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('e')
            ->where('(
                SELECT MAX(et.ends_at)
                FROM OHMedia\EventBundle\Entity\EventTime et
                WHERE IDENTITY(et.event) = e.id
            ) > :now')
            ->setParameter('now', DateTimeUtil::getDateTimeUtc())
        ;
    }

    public function getPastQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('e')
            ->where('(
                SELECT MAX(et.ends_at)
                FROM OHMedia\EventBundle\Entity\EventTime et
                WHERE IDENTITY(et.event) = e.id
            ) < :now')
            ->setParameter('now', DateTimeUtil::getDateTimeUtc())
        ;
    }

    public function getUpcomingQueryBuilderOrdered(): QueryBuilder
    {
        return $this->getUpcomingQueryBuilder()
            ->addSelect('(
                SELECT MIN(et2.starts_at)
                FROM OHMedia\EventBundle\Entity\EventTime et2
                WHERE IDENTITY(et2.event) = e.id
                AND et2.ends_at > :now
            ) AS HIDDEN starts_at')
            ->orderBy('starts_at', 'ASC');
    }

    public function getPastQueryBuilderOrdered(): QueryBuilder
    {
        return $this->getPastQueryBuilder()
            ->addSelect('(
                SELECT MAX(et2.ends_at)
                FROM OHMedia\EventBundle\Entity\EventTime et2
                WHERE IDENTITY(et2.event) = e.id
            ) AS HIDDEN ends_at')
            ->orderBy('ends_at', 'DESC');
    }

    public function getFrontendQueryBuilder(): QueryBuilder
    {
        return $this->getUpcomingQueryBuilderOrdered()
            ->andWhere('e.published_at IS NOT NULL')
            ->andWhere('e.published_at < :now')
        ;
    }

    public function containsWysiwygShortcodes(string ...$shortcodes): bool
    {
        $ors = [];
        $params = new ArrayCollection();

        foreach ($shortcodes as $i => $shortcode) {
            $ors[] = 'e.description LIKE :shortcode_'.$i;
            $params[] = new Parameter('shortcode_'.$i, '%'.$shortcode.'%');
        }

        return $this->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->where(implode(' OR ', $ors))
            ->setParameters($params)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }
}
