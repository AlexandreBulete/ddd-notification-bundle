<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Identity;

use AlexandreBulete\DddNotificationBundle\Domain\Service\IdentityGeneratorInterface;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\SubscriptionId;

final readonly class UlidIdentityGenerator implements IdentityGeneratorInterface
{
    public function nextRecipientId(): RecipientId
    {
        return RecipientId::generate();
    }

    public function nextSubscriptionId(): SubscriptionId
    {
        return SubscriptionId::generate();
    }
}
