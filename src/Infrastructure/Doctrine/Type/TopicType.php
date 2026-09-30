<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine\Type;

use AlexandreBulete\DddDoctrineBridge\Type\VarcharType;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Topic;

final class TopicType extends VarcharType
{
    public const NAME = 'notification_topic';

    protected string $name = self::NAME;
    protected int $length = 120;
    protected string $voClass = Topic::class;
}
