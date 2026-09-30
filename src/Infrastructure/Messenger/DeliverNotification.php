<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Messenger;

use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;

/**
 * One delivery, deferred to the worker (ADR 0010): plain and serializable.
 */
final readonly class DeliverNotification
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
