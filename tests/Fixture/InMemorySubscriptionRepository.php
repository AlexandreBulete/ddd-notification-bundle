<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Tests\Fixture;

use AlexandreBulete\DddFoundation\Domain\ValueObject\IdentifierVO;
use AlexandreBulete\DddFoundation\Infrastructure\InMemory\InMemoryRepository;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Subscription;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\SubscriptionRepositoryInterface;

/**
 * @extends InMemoryRepository<Subscription>
 */
final class InMemorySubscriptionRepository extends InMemoryRepository implements SubscriptionRepositoryInterface
{
    public function save(Subscription $e): void
    {
        $this->entities[(string) $e->id] = $e;
    }

    public function remove(Subscription $e): void
    {
        unset($this->entities[(string) $e->id]);
    }

    public function findById(IdentifierVO $id): ?Subscription
    {
        return $this->entities[(string) $id] ?? null;
    }

    public function forTopic(\AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Topic $topic): array
    {
        return array_values(array_filter($this->entities, static fn (Subscription $s): bool => $s->topic->equals($topic)));
    }

    public function findFor(\AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId $recipientId, \AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Topic $topic): ?Subscription
    {
        foreach ($this->entities as $subscription) {
            if ($subscription->recipientId->equals($recipientId) && $subscription->topic->equals($topic)) {
                return $subscription;
            }
        }

        return null;
    }

    public function countForRecipient(\AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId $recipientId): int
    {
        return count(array_filter($this->entities, static fn (Subscription $s): bool => $s->recipientId->equals($recipientId)));
    }
}
