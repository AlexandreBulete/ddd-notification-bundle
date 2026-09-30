<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Command\Subscribe;

use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Subscription;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\SubscriptionRepositoryInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Service\IdentityGeneratorInterface;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class SubscribeHandler
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptions,
        private IdentityGeneratorInterface $identities,
        private ClockInterface $clock,
    ) {}

    public function __invoke(SubscribeCommand $command): Subscription
    {
        $subscription = Subscription::create(
            $this->identities->nextSubscriptionId(),
            $command->recipientId,
            $command->topic,
            $command->channels,
            $this->clock->now(),
        );
        $this->subscriptions->save($subscription);

        return $subscription;
    }
}
