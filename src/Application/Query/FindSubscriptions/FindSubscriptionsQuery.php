<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Query\FindSubscriptions;

use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\SubscriptionRepositoryInterface;

/**
 * @implements QueryInterface<SubscriptionRepositoryInterface>
 */
final readonly class FindSubscriptionsQuery implements QueryInterface
{
    /**
     * @param array<string, mixed>  $criteria
     * @param array<string, string> $withSorting
     */
    public function __construct(
        public ?int $page = null,
        public ?int $itemsPerPage = null,
        public array $criteria = [],
        public array $withSorting = [],
    ) {}
}
