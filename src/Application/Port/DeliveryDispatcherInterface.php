<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Port;

use AlexandreBulete\DddNotificationBundle\Application\Delivery\PendingDelivery;

/**
 * Defers a delivery off the request (ADR 0010/0014): the Application states
 * the intent, the adapter puts it on the async transport. A slow or failing
 * channel then never blocks the action that triggered the notification, and
 * each delivery retries on its own.
 */
interface DeliveryDispatcherInterface
{
    public function dispatch(PendingDelivery $delivery): void;
}
