<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Domain\ValueObject;

use AlexandreBulete\DddFoundation\Domain\Trait\AsSelectableEnum;

/**
 * A delivery channel. Each maps to a Symfony Notifier transport; a recipient
 * is reachable on a channel only if it has a handle for it.
 */
enum Channel: string
{
    use AsSelectableEnum;

    case Slack = 'slack';
    case Email = 'email';
    case Sms = 'sms';
    case WhatsApp = 'whatsapp';
}
