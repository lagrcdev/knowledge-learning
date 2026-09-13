<?php

namespace App\Service;

use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Entity\Purchase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

// Business logic for purchases: this is the layer between the controllers
// and the data-access components (repositories), so controllers stay thin.
class PurchaseService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    // A non-verified account cannot buy anything, as required by the brief.
    public function canPurchase(User $user): bool
    {
        return $user->isVerified();
    }

    public function recordCursusPurchase(User $user, Cursus $cursus): Purchase
    {
        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setType(Purchase::TYPE_CURSUS);
        $purchase->setCursus($cursus);
        $purchase->setAmount($cursus->getPrice());
        $purchase->setPurchasedAt(new \DateTimeImmutable());

        $this->entityManager->persist($purchase);
        $this->entityManager->flush();

        return $purchase;
    }

    public function recordLessonPurchase(User $user, Lesson $lesson): Purchase
    {
        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setType(Purchase::TYPE_LESSON);
        $purchase->setLesson($lesson);
        $purchase->setAmount($lesson->getPrice());
        $purchase->setPurchasedAt(new \DateTimeImmutable());

        $this->entityManager->persist($purchase);
        $this->entityManager->flush();

        return $purchase;
    }
}
