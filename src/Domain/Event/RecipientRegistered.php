<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

final readonly class RecipientRegistered implements DomainEvent
{
    public function __construct(
        public string $recipientId,
        public string $name,
    ) {}
}
