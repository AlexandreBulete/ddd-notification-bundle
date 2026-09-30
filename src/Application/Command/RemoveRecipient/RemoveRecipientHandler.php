<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Command\RemoveRecipient;

use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use AlexandreBulete\DddNotificationBundle\Domain\Exception\RecipientStillSubscribedException;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Recipient;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\RecipientRepositoryInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\SubscriptionRepositoryInterface;

#[AsCommandHandler]
final readonly class RemoveRecipientHandler
{
    public function __construct(
        private RecipientRepositoryInterface $recipients,
        private SubscriptionRepositoryInterface $subscriptions,
    ) {}

    public function __invoke(RemoveRecipientCommand $command): void
    {
        $recipient = $this->recipients->findById($command->id)
            ?? throw new EntityNotFoundException(Recipient::class, $command->id);

        $count = $this->subscriptions->countForRecipient($command->id);
        if ($count > 0) {
            throw new RecipientStillSubscribedException($command->id, $count);
        }

        $this->recipients->remove($recipient);
    }
}
