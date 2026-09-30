<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Port;

use AlexandreBulete\DddNotificationBundle\Application\Delivery\PendingDelivery;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;

/**
 * Delivers a notification on one channel. One adapter per channel (Slack,
 * email, SMS, WhatsApp), each bridging to a Symfony Notifier transport.
 */
interface ChannelSenderInterface
{
    public function supports(Channel $channel): bool;

    public function send(PendingDelivery $delivery): void;
}
