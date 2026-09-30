<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use AlexandreBulete\DddNotificationBundle\Application\Command\RemoveSubscription\RemoveSubscriptionCommand;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\SubscriptionId;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Resource\SubscriptionResource;
use Symfony\Component\Uid\Ulid;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Webmozart\Assert\Assert;

final readonly class DeleteSubscriptionProcessor implements ProcessorInterface
{
    public function __construct(private CommandBusInterface $commandBus) {}

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        Assert::isInstanceOf($data, SubscriptionResource::class);
        Assert::isInstanceOf($data->id, Ulid::class);

        $this->commandBus->dispatch(new RemoveSubscriptionCommand(SubscriptionId::fromUlid($data->id)));

        return null;
    }
}
