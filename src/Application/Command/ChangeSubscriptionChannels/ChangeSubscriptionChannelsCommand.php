<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Command\ChangeSubscriptionChannels;

use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Subscription;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\SubscriptionId;

/**
 * @implements CommandInterface<Subscription>
 */
final readonly class ChangeSubscriptionChannelsCommand implements CommandInterface
{
    /**
     * @param list<Channel> $channels
     */
    public function __construct(
        public SubscriptionId $id,
        public array $channels,
    ) {}
}
