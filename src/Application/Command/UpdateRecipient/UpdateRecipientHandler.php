<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Command\UpdateRecipient;

use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Recipient;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\RecipientRepositoryInterface;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class UpdateRecipientHandler
{
    public function __construct(
        private RecipientRepositoryInterface $recipients,
        private ClockInterface $clock,
    ) {}

    public function __invoke(UpdateRecipientCommand $command): Recipient
    {
        $recipient = $this->recipients->findById($command->id)
            ?? throw new EntityNotFoundException(Recipient::class, $command->id);

        $now = $this->clock->now();
        $recipient->rename($command->name, $now);
        foreach ($command->handles as $channelValue => $address) {
            $recipient->setHandle(Channel::from($channelValue), $address, $now);
        }
        $this->recipients->save($recipient);

        return $recipient;
    }
}
