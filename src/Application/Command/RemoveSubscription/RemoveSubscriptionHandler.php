<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Command\RemoveSubscription;

use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Subscription;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\SubscriptionRepositoryInterface;

#[AsCommandHandler]
final readonly class RemoveSubscriptionHandler
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptions,
    ) {}

    public function __invoke(RemoveSubscriptionCommand $command): void
    {
        $subscription = $this->subscriptions->findById($command->id)
            ?? throw new EntityNotFoundException(Subscription::class, $command->id);

        $this->subscriptions->remove($subscription);
    }
}
