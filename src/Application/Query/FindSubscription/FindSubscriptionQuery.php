<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Query\FindSubscription;

use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Subscription;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\SubscriptionId;

/**
 * @implements QueryInterface<Subscription>
 */
final readonly class FindSubscriptionQuery implements QueryInterface
{
    public function __construct(public SubscriptionId $id) {}
}
