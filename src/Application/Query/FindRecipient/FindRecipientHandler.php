<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Query\FindRecipient;

use AlexandreBulete\DddFoundation\Application\Handler\QuerySingleHandler;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Recipient;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\RecipientRepositoryInterface;

/**
 * @extends QuerySingleHandler<Recipient>
 */
#[AsQueryHandler]
final readonly class FindRecipientHandler extends QuerySingleHandler
{
    public function __construct(RecipientRepositoryInterface $recipients)
    {
        parent::__construct($recipients);
    }

    public function __invoke(FindRecipientQuery $query): Recipient
    {
        return $this->build($query);
    }
}
