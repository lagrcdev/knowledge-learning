<?php

namespace App\Repository;

use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Entity\Purchase;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Purchase>
 *
 * This is the data-access component (DAO/Repository pattern) for purchases.
 */
class PurchaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Purchase::class);
    }

    /**
     * A user has access to a cursus if they bought that cursus directly.
     */
    public function hasAccessToCursus(User $user, Cursus $cursus): bool
    {
        $count = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.user = :user')
            ->andWhere('p.cursus = :cursus')
            ->setParameter('user', $user)
            ->setParameter('cursus', $cursus)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * A user has access to a lesson if they bought that lesson directly,
     * OR if they bought its parent cursus as a whole.
     */
    public function hasAccessToLesson(User $user, Lesson $lesson): bool
    {
        $count = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.user = :user')
            ->andWhere('p.lesson = :lesson OR p.cursus = :cursus')
            ->setParameter('user', $user)
            ->setParameter('lesson', $lesson)
            ->setParameter('cursus', $lesson->getCursus())
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
