<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Provider;

use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use AlexandreBulete\DddNotificationBundle\Application\Query\FindRecipient\FindRecipientQuery;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Resource\RecipientResource;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\RequestOption;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProviderInterface;

final readonly class RecipientItemProvider implements ProviderInterface
{
    public function __construct(private QueryBusInterface $queryBus) {}

    public function provide(Operation $operation, Context $context): ?RecipientResource
    {
        $id = $context->get(RequestOption::class)?->request()->attributes->getString('id');
        if ($id === null || $id === '') {
            return null;
        }

        return RecipientResource::fromModel($this->queryBus->ask(new FindRecipientQuery(RecipientId::fromString($id))));
    }
}
