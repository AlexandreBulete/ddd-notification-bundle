<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Port;

use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Notification;

/**
 * What the application calls to tell people something happened (ADR 0014). It
 * names a topic and a message; who receives it, and on which channel, is
 * decided by the subscriptions — not by the caller.
 */
interface NotifierInterface
{
    public function notify(Notification $notification): void;
}
