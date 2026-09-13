<?php

namespace App\Tests\Repository;

use App\Entity\Lesson;
use App\Repository\LessonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LessonRepositoryTest extends KernelTestCase
{
    private LessonRepository $lessonRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->lessonRepository = self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Lesson::class);
    }

    // Functional test: a lesson from the fixtures has the right price and belongs to a cursus
    public function testFindLessonByTitle(): void
    {
        $lesson = $this->lessonRepository->findOneBy(['title' => 'Les langages Html et CSS']);

        $this->assertNotNull($lesson);
        $this->assertSame(32.0, $lesson->getPrice());
        $this->assertNotNull($lesson->getCursus());
    }

    // Defensive test: an unknown id must not throw an exception
    public function testFindNonExistentLessonReturnsNull(): void
    {
        $this->assertNull($this->lessonRepository->find(999999));
    }
}
