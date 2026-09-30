<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use AlexandreBulete\DddNotificationBundle\Application\Command\Subscribe\SubscribeCommand;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Topic;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Resource\SubscriptionResource;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Webmozart\Assert\Assert;

final readonly class CreateSubscriptionProcessor implements ProcessorInterface
{
    public function __construct(private CommandBusInterface $commandBus)
    {
    }

    public function process(mixed $data, Operation $operation, Context $context): SubscriptionResource
    {
        Assert::isInstanceOf($data, SubscriptionResource::class);
        Assert::stringNotEmpty($data->recipientId);
        Assert::stringNotEmpty($data->topic);

        $subscription = $this->commandBus->dispatch(new SubscribeCommand(
            RecipientId::fromString($data->recipientId),
            new Topic($data->topic),
            array_map(static fn (string $c): Channel => Channel::from($c), $data->channels),
        ));

        return SubscriptionResource::fromModel($subscription, (string) $data->recipientName);
    }
}
