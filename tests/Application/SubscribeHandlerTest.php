<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Tests\Application;

use AlexandreBulete\DddNotificationBundle\Application\Command\Subscribe\SubscribeCommand;
use AlexandreBulete\DddNotificationBundle\Application\Command\Subscribe\SubscribeHandler;
use AlexandreBulete\DddNotificationBundle\Domain\Service\IdentityGeneratorInterface;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\SubscriptionId;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Topic;
use AlexandreBulete\DddNotificationBundle\Tests\Fixture\InMemorySubscriptionRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class SubscribeHandlerTest extends TestCase
{
    #[Test]
    public function subscribing_again_to_the_same_topic_updates_the_channels_instead_of_duplicating(): void
    {
        $subscriptions = new InMemorySubscriptionRepository();
        $handler = new SubscribeHandler($subscriptions, $this->identities(), new MockClock());
        $recipient = RecipientId::generate();
        $topic = new Topic('scan.vulnerability_found');

        $first = $handler(new SubscribeCommand($recipient, $topic, [Channel::Slack]));
        $again = $handler(new SubscribeCommand($recipient, $topic, [Channel::Slack, Channel::Email]));

        self::assertSame((string) $first->id, (string) $again->id);
        self::assertSame(1, $subscriptions->count());
        self::assertSame([Channel::Slack, Channel::Email], $again->channels);
    }

    #[Test]
    public function the_same_recipient_can_subscribe_to_different_topics(): void
    {
        $subscriptions = new InMemorySubscriptionRepository();
        $handler = new SubscribeHandler($subscriptions, $this->identities(), new MockClock());
        $recipient = RecipientId::generate();

        $handler(new SubscribeCommand($recipient, new Topic('scan.vulnerability_found'), [Channel::Slack]));
        $handler(new SubscribeCommand($recipient, new Topic('mission.deployment_done'), [Channel::Email]));

        self::assertSame(2, $subscriptions->count());
    }

    private function identities(): IdentityGeneratorInterface
    {
        return new class implements IdentityGeneratorInterface {
            public function nextRecipientId(): RecipientId
            {
                return RecipientId::generate();
            }

            public function nextSubscriptionId(): SubscriptionId
            {
                return SubscriptionId::generate();
            }
        };
    }
}
