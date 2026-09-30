<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Command\RemoveRecipient;

use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId;

/**
 * @implements CommandInterface<void>
 */
final readonly class RemoveRecipientCommand implements CommandInterface
{
    public function __construct(public RecipientId $id) {}
}
