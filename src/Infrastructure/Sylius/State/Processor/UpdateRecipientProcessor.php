<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use AlexandreBulete\DddNotificationBundle\Application\Command\UpdateRecipient\UpdateRecipientCommand;
use AlexandreBulete\DddNotificationBundle\Application\Query\FindRecipient\FindRecipientQuery;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Resource\RecipientResource;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

/**
 * Only a real change becomes a command (mirrors Portfolio's UpdateClient): an
 * untouched save must not dispatch an update that did not happen.
 */
final readonly class UpdateRecipientProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
    ) {
    }

    public function process(mixed $data, Operation $operation, Context $context): RecipientResource
    {
        Assert::isInstanceOf($data, RecipientResource::class);
        Assert::isInstanceOf($data->id, Ulid::class);
        Assert::stringNotEmpty($data->name);

        $id = RecipientId::fromUlid($data->id);
        $current = RecipientResource::fromModel($this->queryBus->ask(new FindRecipientQuery($id)));

        if ($data->name === $current->name && $data->handles() === $current->handles()) {
            return $current;
        }

        return RecipientResource::fromModel($this->commandBus->dispatch(new UpdateRecipientCommand(
            $id,
            $data->name,
            $data->handles(),
        )));
    }
}
