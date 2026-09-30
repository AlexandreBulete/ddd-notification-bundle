<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine\Type;

use AlexandreBulete\DddDoctrineBridge\Type\GuidType;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\SubscriptionId;

final class SubscriptionIdType extends GuidType
{
    public const NAME = 'notification_subscription_id';

    protected string $name = self::NAME;
    protected string $voClass = SubscriptionId::class;
}
