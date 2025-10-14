<?php

namespace OHMedia\EventBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use OHMedia\EventBundle\Entity\EventTime;

/**
 * @extends ServiceEntityRepository<EventTime>
 *
 * @method EventTime|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventTime|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventTime[]    findAll()
 * @method EventTime[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventTimeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventTime::class);
    }
}
