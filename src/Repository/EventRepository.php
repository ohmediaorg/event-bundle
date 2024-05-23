<?php

namespace OHMedia\EventBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use OHMedia\EventBundle\Entity\Event;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
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

    public function countBySlug(string $slug, int $id = null)
    {
        $params = [
            new Parameter('slug', $slug),
        ];

        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.slug = :slug');

        if ($id) {
            $qb->andWhere('e.id <> :id');

            $params[] = new Parameter('id', $id);
        }

        return $qb->setParameters(new ArrayCollection($params))
            ->getQuery()
            ->getSingleScalarResult();
    }
}
