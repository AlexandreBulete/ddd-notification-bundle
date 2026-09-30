<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Command\SetRecipientHandle;

use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Recipient;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId;

/**
 * @implements CommandInterface<Recipient>
 */
final readonly class SetRecipientHandleCommand implements CommandInterface
{
    public function __construct(
        public RecipientId $recipientId,
        public Channel $channel,
        public ?string $address,
    ) {}
}
