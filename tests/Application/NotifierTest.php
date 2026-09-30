<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Tests\Application;

use AlexandreBulete\DddNotificationBundle\Application\Delivery\PendingDelivery;
use AlexandreBulete\DddNotificationBundle\Application\Port\DeliveryDispatcherInterface;
use AlexandreBulete\DddNotificationBundle\Application\Service\Notifier;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Recipient;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Subscription;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Notification;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\SubscriptionId;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Topic;
use AlexandreBulete\DddNotificationBundle\Tests\Fixture\CapturingDeliveryDispatcher;
use AlexandreBulete\DddNotificationBundle\Tests\Fixture\InMemoryRecipientRepository;
use AlexandreBulete\DddNotificationBundle\Tests\Fixture\InMemorySubscriptionRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NotifierTest extends TestCase
{
    private InMemoryRecipientRepository $recipients;
    private InMemorySubscriptionRepository $subscriptions;
    private CapturingDeliveryDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->recipients = new InMemoryRecipientRepository();
        $this->subscriptions = new InMemorySubscriptionRepository();
    }

    #[Test]
    public function it_delivers_on_each_channel_the_recipient_is_reachable_on(): void
    {
        $quentin = $this->recipient('Quentin', ['slack' => '#alerts']);
        $this->subscribe($quentin, 'scan.vulnerability_found', [Channel::Slack, Channel::Email]);

        $this->notifier()->notify(new Notification(new Topic('scan.vulnerability_found'), 'Vuln', 'body'));

        // Slack only: no email handle, so that channel is skipped, not an error.
        self::assertCount(1, $this->dispatcher->deliveries);
        self::assertSame(Channel::Slack, $this->dispatcher->deliveries[0]->channel);
        self::assertSame('#alerts', $this->dispatcher->deliveries[0]->address);
        self::assertSame('Quentin', $this->dispatcher->deliveries[0]->recipientName);
    }

    #[Test]
    public function each_subscriber_gets_it_on_their_own_channel(): void
    {
        $quentin = $this->recipient('Quentin', ['slack' => '@q']);
        $alex = $this->recipient('Alex', ['sms' => '+33600000000', 'email' => 'a@b.fr']);
        $this->subscribe($quentin, 'scan.vulnerability_found', [Channel::Slack]);
        $this->subscribe($alex, 'scan.vulnerability_found', [Channel::Sms]);

        $this->notifier()->notify(new Notification(new Topic('scan.vulnerability_found'), 'Vuln', 'b'));

        $byChannel = [];
        foreach ($this->dispatcher->deliveries as $d) {
            $byChannel[$d->channel->value] = $d->recipientName;
        }
        self::assertSame(['slack' => 'Quentin', 'sms' => 'Alex'], $byChannel);
    }

    #[Test]
    public function a_topic_nobody_subscribes_to_sends_nothing(): void
    {
        $this->recipient('Quentin', ['slack' => '@q']);

        $this->notifier()->notify(new Notification(new Topic('mission.deployment_done'), 'x', 'y'));

        self::assertSame([], $this->dispatcher->deliveries);
    }

    /**
     * @param array<string, string> $handles channel value => address
     */
    private function recipient(string $name, array $handles): Recipient
    {
        $recipient = Recipient::register(RecipientId::generate(), $name, new \DateTimeImmutable());
        foreach ($handles as $channelValue => $address) {
            $recipient->setHandle(Channel::from($channelValue), $address, new \DateTimeImmutable());
        }
        $this->recipients->save($recipient);

        return $recipient;
    }

    /**
     * @param list<Channel> $channels
     */
    private function subscribe(Recipient $recipient, string $topic, array $channels): void
    {
        $this->subscriptions->save(Subscription::create(
            SubscriptionId::generate(),
            $recipient->id,
            new Topic($topic),
            $channels,
            new \DateTimeImmutable(),
        ));
    }

    private function notifier(): Notifier
    {
        $dispatcher = new CapturingDeliveryDispatcher();
        $notifier = new Notifier($this->subscriptions, $this->recipients, $dispatcher);
        $this->dispatcher = $dispatcher;

        return $notifier;
    }
}
