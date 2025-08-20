<?php

namespace OHMedia\EventBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use OHMedia\EventBundle\Entity\EventTag;

/**
 * @method EventTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventTag[]    findAll()
 * @method EventTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventTag::class);
    }

    public function save(EventTag $eventTag, bool $flush = false): void
    {
        $this->getEntityManager()->persist($eventTag);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EventTag $eventTag, bool $flush = false): void
    {
        $this->getEntityManager()->remove($eventTag);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
