<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Command\UpdateRecipient;

use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Recipient;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId;

/**
 * @implements CommandInterface<Recipient>
 */
final readonly class UpdateRecipientCommand implements CommandInterface
{
    /**
     * @param array<string, ?string> $handles channel value => address (null clears it)
     */
    public function __construct(
        public RecipientId $id,
        public string $name,
        public array $handles,
    ) {}
}
