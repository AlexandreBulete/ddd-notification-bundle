<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use AlexandreBulete\DddNotificationBundle\Application\Command\RegisterRecipient\RegisterRecipientCommand;
use AlexandreBulete\DddNotificationBundle\Application\Command\UpdateRecipient\UpdateRecipientCommand;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Recipient;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Resource\RecipientResource;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Webmozart\Assert\Assert;

/**
 * Register the recipient, then set its handles in one follow-up command — the
 * aggregate is created named, addresses are attached afterwards (each is
 * optional, and null leaves the channel unset).
 */
final readonly class CreateRecipientProcessor implements ProcessorInterface
{
    public function __construct(private CommandBusInterface $commandBus)
    {
    }

    public function process(mixed $data, Operation $operation, Context $context): RecipientResource
    {
        Assert::isInstanceOf($data, RecipientResource::class);
        Assert::stringNotEmpty($data->name);

        /** @var Recipient $recipient */
        $recipient = $this->commandBus->dispatch(new RegisterRecipientCommand($data->name));

        /** @var Recipient $recipient */
        $recipient = $this->commandBus->dispatch(new UpdateRecipientCommand($recipient->id, $data->name, $data->handles()));

        return RecipientResource::fromModel($recipient);
    }
}
