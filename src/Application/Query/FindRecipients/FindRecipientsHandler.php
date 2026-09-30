<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Query\FindRecipients;

use AlexandreBulete\DddFoundation\Application\Handler\QueryCollectionHandler;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;
use AlexandreBulete\DddFoundation\Domain\Repository\RepositoryInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Recipient;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\RecipientRepositoryInterface;

/**
 * @extends QueryCollectionHandler<Recipient>
 */
#[AsQueryHandler]
final readonly class FindRecipientsHandler extends QueryCollectionHandler
{
    public function __construct(RecipientRepositoryInterface $recipients)
    {
        parent::__construct($recipients);
    }

    /**
     * @return RepositoryInterface<Recipient>
     */
    public function __invoke(FindRecipientsQuery $query): RepositoryInterface
    {
        return $this->build($query);
    }
}
