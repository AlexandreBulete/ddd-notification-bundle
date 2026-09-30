<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

final readonly class SubscriptionChanged implements DomainEvent
{
    public function __construct(
        public string $subscriptionId,
        public string $recipientId,
        public string $topic,
    ) {}
}
