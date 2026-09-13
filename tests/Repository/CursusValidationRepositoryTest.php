<?php

namespace App\Tests\Repository;

use App\Entity\Cursus;
use App\Entity\CursusValidation;
use App\Entity\User;
use App\Repository\CursusValidationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CursusValidationRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private CursusValidationRepository $cursusValidationRepository;
    private User $client;
    private User $otherUser;
    private Cursus $cursus;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->cursusValidationRepository = $this->entityManager->getRepository(CursusValidation::class);

        $userRepository = $this->entityManager->getRepository(User::class);
        $this->client = $userRepository->findOneBy(['email' => 'client@example.com']);
        $this->otherUser = $userRepository->findOneBy(['email' => 'admin@knowledge-learning.com']);
        $this->cursus = $this->entityManager->getRepository(Cursus::class)
            ->findOneBy(['name' => 'Cursus d\'initiation à la cuisine']);

        foreach ($this->cursusValidationRepository->findBy(['user' => $this->client]) as $validation) {
            $this->entityManager->remove($validation);
        }
        $this->entityManager->flush();
    }

    // Functional test: a certification appears in findByUser() once created
    public function testCertificationAppearsInFindByUser(): void
    {
        $validation = new CursusValidation();
        $validation->setUser($this->client);
        $validation->setCursus($this->cursus);
        $validation->setValidatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($validation);
        $this->entityManager->flush();

        $certifications = $this->cursusValidationRepository->findByUser($this->client);
        $cursusIds = array_map(fn ($c) => $c->getCursus()->getId(), $certifications);

        $this->assertContains($this->cursus->getId(), $cursusIds);
    }

    // Security test: a certification belonging to one user must not be
    // returned for another user
    public function testCertificationsAreIsolatedPerUser(): void
    {
        $validation = new CursusValidation();
        $validation->setUser($this->client);
        $validation->setCursus($this->cursus);
        $validation->setValidatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($validation);
        $this->entityManager->flush();

        $this->assertFalse($this->cursusValidationRepository->isCursusValidatedByUser($this->otherUser, $this->cursus));
    }
}
