<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Messenger;

use AlexandreBulete\DddNotificationBundle\Application\Delivery\PendingDelivery;
use AlexandreBulete\DddNotificationBundle\Application\Port\DeliveryDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Puts a delivery on the async transport (ADR 0010/0014): sent within the
 * transaction of the action that notified, so a notification is never lost and
 * a rolled-back action sends nothing.
 */
final readonly class MessengerDeliveryDispatcher implements DeliveryDispatcherInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
    ) {}

    public function dispatch(PendingDelivery $delivery): void
    {
        $this->commandBus->dispatch(new DeliverNotification(
            topic: $delivery->topic,
            recipientName: $delivery->recipientName,
            channel: $delivery->channel,
            address: $delivery->address,
            subject: $delivery->subject,
            body: $delivery->body,
        ));
    }
}
