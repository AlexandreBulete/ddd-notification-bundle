<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Tests\Fixture;

use AlexandreBulete\DddNotificationBundle\Application\Delivery\PendingDelivery;
use AlexandreBulete\DddNotificationBundle\Application\Port\DeliveryDispatcherInterface;

final class CapturingDeliveryDispatcher implements DeliveryDispatcherInterface
{
    /** @var list<PendingDelivery> */
    public array $deliveries = [];

    public function dispatch(PendingDelivery $delivery): void
    {
        $this->deliveries[] = $delivery;
    }
}
