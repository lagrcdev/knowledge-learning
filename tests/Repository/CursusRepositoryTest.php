<?php

namespace App\Tests\Repository;

use App\Entity\Cursus;
use App\Repository\CursusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CursusRepositoryTest extends KernelTestCase
{
    private CursusRepository $cursusRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->cursusRepository = self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Cursus::class);
    }

    // Functional test: a cursus from the fixtures is found with the right price
    public function testFindCursusByName(): void
    {
        $cursus = $this->cursusRepository->findOneBy(['name' => 'Cursus d\'initiation au jardinage']);

        $this->assertNotNull($cursus);
        $this->assertSame(30.0, $cursus->getPrice());
        $this->assertSame('Jardinage', $cursus->getTheme()->getName());
    }

    // Defensive test: an unknown id must not throw an exception
    public function testFindNonExistentCursusReturnsNull(): void
    {
        $this->assertNull($this->cursusRepository->find(999999));
    }
}
