<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Domain\Repository;

use AlexandreBulete\DddFoundation\Domain\Repository\RepositoryInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Subscription;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Topic;

/**
 * @extends RepositoryInterface<Subscription>
 */
interface SubscriptionRepositoryInterface extends RepositoryInterface
{
    public function save(Subscription $subscription): void;

    public function remove(Subscription $subscription): void;

    /**
     * The subscriptions to a topic — how a notification finds its audience.
     *
     * @return list<Subscription>
     */
    public function forTopic(Topic $topic): array;

    /**
     * The one subscription of a recipient to a topic, if any: a recipient is
     * subscribed to a topic at most once (their channels for it live there).
     */
    public function findFor(RecipientId $recipientId, Topic $topic): ?Subscription;

    /**
     * @return int<0, max>
     */
    public function countForRecipient(RecipientId $recipientId): int;
}
