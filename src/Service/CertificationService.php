<?php

namespace App\Service;

use App\Entity\CursusValidation;
use App\Entity\Lesson;
use App\Entity\LessonValidation;
use App\Entity\User;
use App\Repository\CursusValidationRepository;
use App\Repository\LessonValidationRepository;
use Doctrine\ORM\EntityManagerInterface;

// Business logic for lesson validation and the "Knowledge Learning"
// certification: validating a lesson, and automatically certifying a
// cursus once all of its lessons are validated by the user.
class CertificationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LessonValidationRepository $lessonValidationRepository,
        private readonly CursusValidationRepository $cursusValidationRepository,
    ) {
    }

    public function validateLesson(User $user, Lesson $lesson): void
    {
        if ($this->lessonValidationRepository->isLessonValidatedByUser($user, $lesson)) {
            // Already validated, nothing to do
            return;
        }

        $lessonValidation = new LessonValidation();
        $lessonValidation->setUser($user);
        $lessonValidation->setLesson($lesson);
        $lessonValidation->setValidatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($lessonValidation);
        $this->entityManager->flush();

        $this->checkCursusCompletion($user, $lesson);
    }

    // If every lesson of the cursus is now validated by this user, and the
    // cursus is not already certified, create the certification.
    private function checkCursusCompletion(User $user, Lesson $lesson): void
    {
        $cursus = $lesson->getCursus();
        $totalLessons = $cursus->getLessons()->count();
        $validatedLessons = $this->lessonValidationRepository->countValidatedLessonsForCursus($user, $cursus->getId());

        if ($validatedLessons < $totalLessons) {
            return;
        }

        if ($this->cursusValidationRepository->isCursusValidatedByUser($user, $cursus)) {
            return;
        }

        $cursusValidation = new CursusValidation();
        $cursusValidation->setUser($user);
        $cursusValidation->setCursus($cursus);
        $cursusValidation->setValidatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($cursusValidation);
        $this->entityManager->flush();
    }
}
