<?php

namespace App\Tests\Service;

use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Entity\Purchase;
use App\Entity\User;
use App\Service\PurchaseService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class PurchaseServiceTest extends TestCase
{
    // A non-verified account cannot buy anything, as required by the brief
    public function testNonVerifiedUserCannotPurchase(): void
    {
        $user = new User();
        $user->setIsVerified(false);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $purchaseService = new PurchaseService($entityManager);

        $this->assertFalse($purchaseService->canPurchase($user));
    }

    public function testVerifiedUserCanPurchase(): void
    {
        $user = new User();
        $user->setIsVerified(true);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $purchaseService = new PurchaseService($entityManager);

        $this->assertTrue($purchaseService->canPurchase($user));
    }

    public function testRecordCursusPurchaseCreatesACorrectPurchase(): void
    {
        $user = new User();
        $cursus = new Cursus();
        $cursus->setName('Test cursus');
        $cursus->setPrice(50);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('flush');

        $purchaseService = new PurchaseService($entityManager);
        $purchase = $purchaseService->recordCursusPurchase($user, $cursus);

        $this->assertSame(Purchase::TYPE_CURSUS, $purchase->getType());
        $this->assertSame($cursus, $purchase->getCursus());
        $this->assertSame(50.0, $purchase->getAmount());
    }

    public function testRecordLessonPurchaseCreatesACorrectPurchase(): void
    {
        $user = new User();
        $lesson = new Lesson();
        $lesson->setTitle('Test lesson');
        $lesson->setPrice(26);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('flush');

        $purchaseService = new PurchaseService($entityManager);
        $purchase = $purchaseService->recordLessonPurchase($user, $lesson);

        $this->assertSame(Purchase::TYPE_LESSON, $purchase->getType());
        $this->assertSame($lesson, $purchase->getLesson());
        $this->assertSame(26.0, $purchase->getAmount());
    }
}
