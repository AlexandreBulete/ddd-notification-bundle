<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine\Type;

use AlexandreBulete\DddDoctrineBridge\Type\GuidType;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId;

final class RecipientIdType extends GuidType
{
    public const NAME = 'notification_recipient_id';

    protected string $name = self::NAME;
    protected string $voClass = RecipientId::class;
}
