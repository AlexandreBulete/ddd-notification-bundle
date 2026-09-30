<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Command\RemoveSubscription;

use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\SubscriptionId;

/**
 * @implements CommandInterface<void>
 */
final readonly class RemoveSubscriptionCommand implements CommandInterface
{
    public function __construct(public SubscriptionId $id) {}
}
