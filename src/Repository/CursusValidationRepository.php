<?php

namespace App\Repository;

use App\Entity\Cursus;
use App\Entity\CursusValidation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CursusValidation>
 */
class CursusValidationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CursusValidation::class);
    }

    public function isCursusValidatedByUser(User $user, Cursus $cursus): bool
    {
        $count = $this->createQueryBuilder('cv')
            ->select('COUNT(cv.id)')
            ->andWhere('cv.user = :user')
            ->andWhere('cv.cursus = :cursus')
            ->setParameter('user', $user)
            ->setParameter('cursus', $cursus)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * @return CursusValidation[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('cv')
            ->andWhere('cv.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
