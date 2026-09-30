<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use AlexandreBulete\DddNotificationBundle\Application\Command\ChangeSubscriptionChannels\ChangeSubscriptionChannelsCommand;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\SubscriptionId;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Resource\SubscriptionResource;
use Symfony\Component\Uid\Ulid;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Webmozart\Assert\Assert;

final readonly class UpdateSubscriptionProcessor implements ProcessorInterface
{
    public function __construct(private CommandBusInterface $commandBus) {}

    public function process(mixed $data, Operation $operation, Context $context): SubscriptionResource
    {
        Assert::isInstanceOf($data, SubscriptionResource::class);
        Assert::isInstanceOf($data->id, Ulid::class);

        $subscription = $this->commandBus->dispatch(new ChangeSubscriptionChannelsCommand(
            SubscriptionId::fromUlid($data->id),
            array_map(static fn (string $c): Channel => Channel::from($c), $data->channels),
        ));

        return SubscriptionResource::fromModel($subscription, (string) $data->recipientName);
    }
}
