<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use AlexandreBulete\DddSyliusBundle\Grid\GridPageResolver;
use AlexandreBulete\DddNotificationBundle\Application\Query\FindRecipients\FindRecipientsQuery;
use AlexandreBulete\DddNotificationBundle\Application\Query\FindSubscriptions\FindSubscriptionsQuery;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Resource\SubscriptionResource;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Sylius\Component\Grid\Data\DataProviderInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;

final readonly class SubscriptionGridProvider implements DataProviderInterface
{
    public function __construct(private QueryBusInterface $queryBus) {}

    /**
     * @return PagerfantaInterface<SubscriptionResource>
     */
    public function getData(Grid $grid, Parameters $parameters): PagerfantaInterface
    {
        /** @var array<string, mixed> $criteria */
        $criteria = $parameters->get('criteria', []);
        /** @var array<string, string> $sorting */
        $sorting = $parameters->get('sorting', $grid->getSorting());

        $subscriptions = $this->queryBus->ask(new FindSubscriptionsQuery(
            page: GridPageResolver::getCurrentPage($grid, $parameters),
            itemsPerPage: GridPageResolver::getItemsPerPage($grid, $parameters),
            criteria: $criteria,
            withSorting: $sorting,
        ));

        $names = [];
        foreach ($this->queryBus->ask(new FindRecipientsQuery()) as $recipient) {
            $names[(string) $recipient->id] = $recipient->name;
        }

        $data = [];
        foreach ($subscriptions as $subscription) {
            $data[] = SubscriptionResource::fromModel($subscription, $names[(string) $subscription->recipientId] ?? '?');
        }

        return new Pagerfanta(new FixedAdapter($subscriptions->count(), $data));
    }
}
