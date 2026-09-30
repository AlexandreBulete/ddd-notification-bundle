<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Query\FindSubscriptions;

use AlexandreBulete\DddFoundation\Application\Handler\QueryCollectionHandler;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;
use AlexandreBulete\DddFoundation\Domain\Repository\RepositoryInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Subscription;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\SubscriptionRepositoryInterface;

/**
 * @extends QueryCollectionHandler<Subscription>
 */
#[AsQueryHandler]
final readonly class FindSubscriptionsHandler extends QueryCollectionHandler
{
    public function __construct(SubscriptionRepositoryInterface $subscriptions)
    {
        parent::__construct($subscriptions);
    }

    /**
     * @return RepositoryInterface<Subscription>
     */
    public function __invoke(FindSubscriptionsQuery $query): RepositoryInterface
    {
        return $this->build($query);
    }
}
