<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Command\SetRecipientHandle;

use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Recipient;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\RecipientRepositoryInterface;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class SetRecipientHandleHandler
{
    public function __construct(
        private RecipientRepositoryInterface $recipients,
        private ClockInterface $clock,
    ) {}

    public function __invoke(SetRecipientHandleCommand $command): Recipient
    {
        $recipient = $this->recipients->findById($command->recipientId)
            ?? throw new EntityNotFoundException(Recipient::class, $command->recipientId);

        $recipient->setHandle($command->channel, $command->address, $this->clock->now());
        $this->recipients->save($recipient);

        return $recipient;
    }
}
