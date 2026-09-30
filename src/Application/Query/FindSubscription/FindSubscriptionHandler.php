<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Query\FindSubscription;

use AlexandreBulete\DddFoundation\Application\Handler\QuerySingleHandler;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Subscription;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\SubscriptionRepositoryInterface;

/**
 * @extends QuerySingleHandler<Subscription>
 */
#[AsQueryHandler]
final readonly class FindSubscriptionHandler extends QuerySingleHandler
{
    public function __construct(SubscriptionRepositoryInterface $subscriptions)
    {
        parent::__construct($subscriptions);
    }

    public function __invoke(FindSubscriptionQuery $query): Subscription
    {
        return $this->build($query);
    }
}
