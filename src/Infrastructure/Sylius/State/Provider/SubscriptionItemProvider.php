<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Provider;

use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use AlexandreBulete\DddNotificationBundle\Application\Query\FindRecipient\FindRecipientQuery;
use AlexandreBulete\DddNotificationBundle\Application\Query\FindSubscription\FindSubscriptionQuery;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Recipient;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Subscription;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\SubscriptionId;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Resource\SubscriptionResource;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\RequestOption;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProviderInterface;

final readonly class SubscriptionItemProvider implements ProviderInterface
{
    public function __construct(private QueryBusInterface $queryBus) {}

    public function provide(Operation $operation, Context $context): ?SubscriptionResource
    {
        $id = $context->get(RequestOption::class)?->request()->attributes->getString('id');
        if ($id === null || $id === '') {
            return null;
        }

        /** @var Subscription $subscription */
        $subscription = $this->queryBus->ask(new FindSubscriptionQuery(SubscriptionId::fromString($id)));
        /** @var Recipient $recipient */
        $recipient = $this->queryBus->ask(new FindRecipientQuery(RecipientId::fromString((string) $subscription->recipientId)));

        return SubscriptionResource::fromModel($subscription, $recipient->name);
    }
}
