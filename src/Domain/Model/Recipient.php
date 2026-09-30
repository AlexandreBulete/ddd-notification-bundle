<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Domain\Model;

use AlexandreBulete\DddFoundation\Domain\Model\RecordsEvents;
use AlexandreBulete\DddNotificationBundle\Domain\Event\RecipientChanged;
use AlexandreBulete\DddNotificationBundle\Domain\Event\RecipientRegistered;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId;

/**
 * A person who can be notified (ADR 0014). Holds, per channel, the address to
 * reach them (a Slack id/channel, an email, a phone number). Standalone — not
 * an IAM account: this bundle can live in a project that has no IAM.
 */
final class Recipient
{
    use RecordsEvents;

    /**
     * @param array<string, string> $handles channel value => address
     */
    private function __construct(
        private(set) RecipientId $id,
        private(set) string $name,
        private(set) array $handles,
        private(set) \DateTimeImmutable $createdAt,
        private(set) ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function register(RecipientId $id, string $name, \DateTimeImmutable $at): self
    {
        $recipient = new self($id, self::assertName($name), [], $at, null);
        $recipient->recordEvent(new RecipientRegistered((string) $id, $recipient->name));

        return $recipient;
    }

    public function rename(string $name, \DateTimeImmutable $at): void
    {
        $name = self::assertName($name);
        if ($name === $this->name) {
            return;
        }

        $this->name = $name;
        $this->touch($at);
        $this->recordEvent(new RecipientChanged((string) $this->id));
    }

    /**
     * Sets (or clears, with an empty address) the address for a channel.
     */
    public function setHandle(Channel $channel, ?string $address, \DateTimeImmutable $at): void
    {
        $address = $address === null ? '' : trim($address);
        $handles = $this->handles;
        if ($address === '') {
            unset($handles[$channel->value]);
        } else {
            $handles[$channel->value] = $address;
        }
        if ($handles === $this->handles) {
            return;
        }

        $this->handles = $handles;
        $this->touch($at);
        $this->recordEvent(new RecipientChanged((string) $this->id));
    }

    public function reachableOn(Channel $channel): bool
    {
        return isset($this->handles[$channel->value]);
    }

    public function addressOn(Channel $channel): ?string
    {
        return $this->handles[$channel->value] ?? null;
    }

    private function touch(\DateTimeImmutable $at): void
    {
        $this->updatedAt = $at;
    }

    private static function assertName(string $name): string
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 150) {
            throw new \InvalidArgumentException('A recipient name is between 1 and 150 characters.');
        }

        return $name;
    }
}
