<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Domain\Exception;

use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId;

final class RecipientStillSubscribedException extends \DomainException
{
    public function __construct(RecipientId $id, int $subscriptions)
    {
        parent::__construct(sprintf('Recipient %s still has %d subscription(s).', $id, $subscriptions));
    }
}
