<?php

namespace App\EventSubscriber;

use App\Entity\Trait\BlameableEntityInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Automatically sets createdBy/updatedBy on any entity implementing
 * BlameableEntityInterface, using the currently logged-in user.
 *
 * This is the Observer pattern applied to Doctrine's entity lifecycle:
 * this class "observes" every persist/update and reacts without the
 * entities themselves knowing about the Security component.
 */
#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class BlameableSubscriber
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof BlameableEntityInterface) {
            return;
        }

        $user = $this->security->getUser();

        if ($user === null) {
            return;
        }

        $entity->setCreatedBy($user);
        $entity->setUpdatedBy($user);
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof BlameableEntityInterface) {
            return;
        }

        $user = $this->security->getUser();

        if ($user === null) {
            return;
        }

        $entity->setUpdatedBy($user);

        // Event listeners (unlike lifecycle callbacks declared on the entity
        // itself) do not have their field changes picked up automatically,
        // so we recompute the changeset for this entity.
        $entityManager = $args->getObjectManager();
        $entityManager->getUnitOfWork()->recomputeSingleEntityChangeSet(
            $entityManager->getClassMetadata($entity::class),
            $entity,
        );
    }
}
