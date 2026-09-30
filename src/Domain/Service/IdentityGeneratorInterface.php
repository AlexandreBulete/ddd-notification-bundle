<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Domain\Service;

use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\SubscriptionId;

interface IdentityGeneratorInterface
{
    public function nextRecipientId(): RecipientId;

    public function nextSubscriptionId(): SubscriptionId;
}
