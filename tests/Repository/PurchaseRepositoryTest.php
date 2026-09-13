<?php

namespace App\Tests\Repository;

use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Entity\Purchase;
use App\Entity\User;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PurchaseRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private PurchaseRepository $purchaseRepository;
    private User $buyer;
    private User $strangerUser;
    private Lesson $lesson;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->purchaseRepository = $this->entityManager->getRepository(Purchase::class);

        $userRepository = $this->entityManager->getRepository(User::class);
        $this->buyer = $userRepository->findOneBy(['email' => 'client@example.com']);
        $this->strangerUser = $userRepository->findOneBy(['email' => 'admin@knowledge-learning.com']);

        $this->lesson = $this->entityManager->getRepository(Lesson::class)
            ->findOneBy(['title' => 'Les outils du jardinier']);

        // Make sure this test starts from a clean state
        foreach ($this->purchaseRepository->findBy(['user' => $this->buyer]) as $purchase) {
            $this->entityManager->remove($purchase);
        }
        $this->entityManager->flush();
    }

    // Functional test: a user who bought a lesson has access to it
    public function testUserHasAccessToLessonTheyBought(): void
    {
        $purchase = new Purchase();
        $purchase->setUser($this->buyer);
        $purchase->setType(Purchase::TYPE_LESSON);
        $purchase->setLesson($this->lesson);
        $purchase->setAmount($this->lesson->getPrice());
        $purchase->setPurchasedAt(new \DateTimeImmutable());
        $this->entityManager->persist($purchase);
        $this->entityManager->flush();

        $this->assertTrue($this->purchaseRepository->hasAccessToLesson($this->buyer, $this->lesson));
    }

    // Functional test: buying the whole cursus also grants access to its lessons
    public function testBuyingTheCursusGrantsAccessToItsLessons(): void
    {
        $cursus = $this->lesson->getCursus();

        $purchase = new Purchase();
        $purchase->setUser($this->buyer);
        $purchase->setType(Purchase::TYPE_CURSUS);
        $purchase->setCursus($cursus);
        $purchase->setAmount($cursus->getPrice());
        $purchase->setPurchasedAt(new \DateTimeImmutable());
        $this->entityManager->persist($purchase);
        $this->entityManager->flush();

        $this->assertTrue($this->purchaseRepository->hasAccessToLesson($this->buyer, $this->lesson));
        $this->assertTrue($this->purchaseRepository->hasAccessToCursus($this->buyer, $cursus));
    }

    // Security test: a user who did not buy anything must NOT have access
    public function testUserWithoutPurchaseHasNoAccess(): void
    {
        $this->assertFalse($this->purchaseRepository->hasAccessToLesson($this->strangerUser, $this->lesson));
    }
}
