<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Query\FindRecipient;

use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Recipient;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId;

/**
 * @implements QueryInterface<Recipient>
 */
final readonly class FindRecipientQuery implements QueryInterface
{
    public function __construct(public RecipientId $id) {}
}
