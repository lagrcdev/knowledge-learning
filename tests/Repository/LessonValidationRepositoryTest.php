<?php

namespace App\Tests\Repository;

use App\Entity\Lesson;
use App\Entity\LessonValidation;
use App\Entity\User;
use App\Repository\LessonValidationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LessonValidationRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private LessonValidationRepository $lessonValidationRepository;
    private User $client;
    private User $otherUser;
    private Lesson $lesson;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->lessonValidationRepository = $this->entityManager->getRepository(LessonValidation::class);

        $userRepository = $this->entityManager->getRepository(User::class);
        $this->client = $userRepository->findOneBy(['email' => 'client@example.com']);
        $this->otherUser = $userRepository->findOneBy(['email' => 'admin@knowledge-learning.com']);
        $this->lesson = $this->entityManager->getRepository(Lesson::class)
            ->findOneBy(['title' => 'Les modes de cuisson']);

        foreach ($this->lessonValidationRepository->findBy(['user' => $this->client]) as $validation) {
            $this->entityManager->remove($validation);
        }
        $this->entityManager->flush();
    }

    // Functional test: validating a lesson is then reported as validated
    public function testLessonIsReportedAsValidatedAfterValidation(): void
    {
        $validation = new LessonValidation();
        $validation->setUser($this->client);
        $validation->setLesson($this->lesson);
        $validation->setValidatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($validation);
        $this->entityManager->flush();

        $this->assertTrue($this->lessonValidationRepository->isLessonValidatedByUser($this->client, $this->lesson));
    }

    // Security test: one user's validation must not be visible for another user
    public function testValidationsAreIsolatedPerUser(): void
    {
        $validation = new LessonValidation();
        $validation->setUser($this->client);
        $validation->setLesson($this->lesson);
        $validation->setValidatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($validation);
        $this->entityManager->flush();

        $this->assertFalse($this->lessonValidationRepository->isLessonValidatedByUser($this->otherUser, $this->lesson));
    }
}
