<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Delivery;

use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;

/**
 * One notification to one recipient on one channel — resolved from the
 * subscriptions, handed to the async transport. Plain and serializable: it
 * crosses to the worker (ADR 0010).
 */
final readonly class PendingDelivery
{
    public function __construct(
        public string $topic,
        public string $recipientName,
        public Channel $channel,
        public string $address,
        public string $subject,
        public string $body,
    ) {}
}
