<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Command\ChangeSubscriptionChannels;

use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Subscription;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\SubscriptionRepositoryInterface;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class ChangeSubscriptionChannelsHandler
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptions,
        private ClockInterface $clock,
    ) {}

    public function __invoke(ChangeSubscriptionChannelsCommand $command): Subscription
    {
        $subscription = $this->subscriptions->findById($command->id)
            ?? throw new EntityNotFoundException(Subscription::class, $command->id);

        $subscription->changeChannels($command->channels, $this->clock->now());
        $this->subscriptions->save($subscription);

        return $subscription;
    }
}
