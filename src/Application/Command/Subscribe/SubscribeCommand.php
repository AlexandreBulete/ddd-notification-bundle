<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Command\Subscribe;

use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Subscription;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Topic;

/**
 * Subscribe a recipient to a topic on some channels (ADR 0014).
 *
 * @implements CommandInterface<Subscription>
 */
final readonly class SubscribeCommand implements CommandInterface
{
    /**
     * @param list<Channel> $channels
     */
    public function __construct(
        public RecipientId $recipientId,
        public Topic $topic,
        public array $channels,
    ) {}
}
