<?php

namespace App\Entity\Trait;

use App\Entity\User;

/**
 * Marks an entity as "blameable": it can record which user created it
 * and which user last updated it. Used by BlameableSubscriber.
 */
interface BlameableEntityInterface
{
    public function setCreatedBy(?User $user): static;

    public function setUpdatedBy(?User $user): static;
}
