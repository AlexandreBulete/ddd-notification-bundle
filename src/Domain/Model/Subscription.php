<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Domain\Model;

use AlexandreBulete\DddFoundation\Domain\Model\RecordsEvents;
use AlexandreBulete\DddNotificationBundle\Domain\Event\SubscriptionChanged;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\SubscriptionId;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Topic;

/**
 * The routing rule (ADR 0014): a recipient wants a topic on these channels.
 * "Quentin + scan.vulnerability_found → [slack]."
 */
final class Subscription
{
    use RecordsEvents;

    /**
     * @param list<Channel> $channels
     */
    private function __construct(
        private(set) SubscriptionId $id,
        private(set) RecipientId $recipientId,
        private(set) Topic $topic,
        private(set) array $channels,
        private(set) \DateTimeImmutable $createdAt,
        private(set) ?\DateTimeImmutable $updatedAt,
    ) {}

    /**
     * @param list<Channel> $channels
     */
    public static function create(SubscriptionId $id, RecipientId $recipientId, Topic $topic, array $channels, \DateTimeImmutable $at): self
    {
        $subscription = new self($id, $recipientId, $topic, self::normalize($channels), $at, null);
        $subscription->recordEvent(new SubscriptionChanged((string) $id, (string) $recipientId, $topic->value()));

        return $subscription;
    }

    /**
     * @param list<Channel> $channels
     */
    public function changeChannels(array $channels, \DateTimeImmutable $at): void
    {
        $channels = self::normalize($channels);
        if ($channels === $this->channels) {
            return;
        }

        $this->channels = $channels;
        $this->updatedAt = $at;
        $this->recordEvent(new SubscriptionChanged((string) $this->id, (string) $this->recipientId, $this->topic->value()));
    }

    /**
     * @param list<Channel> $channels
     *
     * @return list<Channel> deduplicated, in a stable order
     */
    private static function normalize(array $channels): array
    {
        $seen = [];
        foreach ($channels as $channel) {
            $seen[$channel->value] = $channel;
        }
        $ordered = [];
        foreach (Channel::cases() as $case) {
            if (isset($seen[$case->value])) {
                $ordered[] = $case;
            }
        }

        return $ordered;
    }
}
