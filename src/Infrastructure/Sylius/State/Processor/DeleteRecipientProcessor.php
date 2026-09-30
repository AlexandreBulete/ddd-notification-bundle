<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use AlexandreBulete\DddNotificationBundle\Application\Command\RemoveRecipient\RemoveRecipientCommand;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Resource\RecipientResource;
use Symfony\Component\Uid\Ulid;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Webmozart\Assert\Assert;

final readonly class DeleteRecipientProcessor implements ProcessorInterface
{
    public function __construct(private CommandBusInterface $commandBus) {}

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        Assert::isInstanceOf($data, RecipientResource::class);
        Assert::isInstanceOf($data->id, Ulid::class);

        $this->commandBus->dispatch(new RemoveRecipientCommand(RecipientId::fromUlid($data->id)));

        return null;
    }
}
