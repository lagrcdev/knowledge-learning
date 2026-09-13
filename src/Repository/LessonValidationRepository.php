<?php

namespace App\Repository;

use App\Entity\Lesson;
use App\Entity\LessonValidation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LessonValidation>
 */
class LessonValidationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LessonValidation::class);
    }

    public function isLessonValidatedByUser(User $user, Lesson $lesson): bool
    {
        $count = $this->createQueryBuilder('lv')
            ->select('COUNT(lv.id)')
            ->andWhere('lv.user = :user')
            ->andWhere('lv.lesson = :lesson')
            ->setParameter('user', $user)
            ->setParameter('lesson', $lesson)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Counts how many lessons of the given cursus this user has validated.
     * Used to check whether a cursus should be automatically certified.
     */
    public function countValidatedLessonsForCursus(User $user, int $cursusId): int
    {
        return (int) $this->createQueryBuilder('lv')
            ->select('COUNT(lv.id)')
            ->join('lv.lesson', 'l')
            ->andWhere('lv.user = :user')
            ->andWhere('l.cursus = :cursusId')
            ->setParameter('user', $user)
            ->setParameter('cursusId', $cursusId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
