<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Domain\Repository;

use AlexandreBulete\DddFoundation\Domain\Repository\RepositoryInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Recipient;

/**
 * @extends RepositoryInterface<Recipient>
 */
interface RecipientRepositoryInterface extends RepositoryInterface
{
    public function save(Recipient $recipient): void;

    public function remove(Recipient $recipient): void;
}
