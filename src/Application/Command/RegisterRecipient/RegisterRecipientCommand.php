<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Command\RegisterRecipient;

use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Recipient;

/**
 * @implements CommandInterface<Recipient>
 */
final readonly class RegisterRecipientCommand implements CommandInterface
{
    public function __construct(
        public string $name,
    ) {}
}
