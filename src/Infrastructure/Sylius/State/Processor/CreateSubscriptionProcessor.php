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

/**
 * One form, one subscription per topic picked — a recipient's topics set in a
 * single step. Subscribing to a topic they already follow updates its channels
 * (the Subscribe use case is idempotent per recipient + topic), never a
 * duplicate.
 */
final readonly class CreateSubscriptionProcessor implements ProcessorInterface
{
    public function __construct(private CommandBusInterface $commandBus)
    {
    }

    public function process(mixed $data, Operation $operation, Context $context): SubscriptionResource
    {
        Assert::isInstanceOf($data, SubscriptionResource::class);
        Assert::stringNotEmpty($data->recipientId);
        Assert::notEmpty($data->topics);

        $recipientId = RecipientId::fromString($data->recipientId);
        $channels = array_map(static fn (string $c): Channel => Channel::from($c), $data->channels);

        // notEmpty above guarantees the loop runs, so $last is a Subscription.
        $last = null;
        foreach ($data->topics as $topic) {
            $last = $this->commandBus->dispatch(new SubscribeCommand($recipientId, new Topic($topic), $channels));
        }

        return SubscriptionResource::fromModel($last, (string) $data->recipientName);
    }
}
