<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Service;

use AlexandreBulete\DddNotificationBundle\Application\Delivery\PendingDelivery;
use AlexandreBulete\DddNotificationBundle\Application\Port\DeliveryDispatcherInterface;
use AlexandreBulete\DddNotificationBundle\Application\Port\NotifierInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Subscription;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\RecipientRepositoryInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\SubscriptionRepositoryInterface;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Notification;

/**
 * Turns a topic into deliveries (ADR 0014): for each subscription to the
 * topic, for each channel the recipient is reachable on, one async delivery.
 * A recipient subscribed on a channel it has no handle for is simply skipped —
 * never an error.
 */
final readonly class Notifier implements NotifierInterface
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptions,
        private RecipientRepositoryInterface $recipients,
        private DeliveryDispatcherInterface $dispatcher,
    ) {}

    public function notify(Notification $notification): void
    {
        foreach ($this->subscriptions->forTopic($notification->topic) as $subscription) {
            $this->deliverSubscription($subscription, $notification);
        }
    }

    private function deliverSubscription(Subscription $subscription, Notification $notification): void
    {
        $recipient = $this->recipients->findById($subscription->recipientId);
        if ($recipient === null) {
            return;
        }

        foreach ($subscription->channels as $channel) {
            $address = $recipient->addressOn($channel);
            if ($address === null) {
                continue;
            }

            $this->dispatcher->dispatch(new PendingDelivery(
                topic: $notification->topic->value(),
                recipientName: $recipient->name,
                channel: $channel,
                address: $address,
                subject: $notification->subject,
                body: $notification->body,
            ));
        }
    }
}
