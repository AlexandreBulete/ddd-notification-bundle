<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Messenger;

use AlexandreBulete\DddNotificationBundle\Application\Delivery\PendingDelivery;
use AlexandreBulete\DddNotificationBundle\Application\Port\ChannelSenderInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Worker glue: routes a delivery to the channel sender that supports it. A
 * channel with no sender wired (its Notifier bridge absent) is logged and
 * skipped — never a crash that would retry forever.
 */
#[AsMessageHandler(bus: 'command.bus')]
final readonly class DeliverNotificationHandler
{
    /**
     * @param iterable<ChannelSenderInterface> $senders
     */
    public function __construct(
        private iterable $senders,
        private \Psr\Log\LoggerInterface $logger,
    ) {}

    public function __invoke(DeliverNotification $message): void
    {
        $delivery = new PendingDelivery(
            topic: $message->topic,
            recipientName: $message->recipientName,
            channel: $message->channel,
            address: $message->address,
            subject: $message->subject,
            body: $message->body,
        );

        foreach ($this->senders as $sender) {
            if ($sender->supports($message->channel)) {
                $sender->send($delivery);

                return;
            }
        }

        $this->logger->warning('No sender for channel {channel}; notification "{subject}" not delivered.', [
            'channel' => $message->channel->value,
            'subject' => $message->subject,
        ]);
    }
}
