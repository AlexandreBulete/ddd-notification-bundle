<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Command\RegisterRecipient;

use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Recipient;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\RecipientRepositoryInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Service\IdentityGeneratorInterface;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class RegisterRecipientHandler
{
    public function __construct(
        private RecipientRepositoryInterface $recipients,
        private IdentityGeneratorInterface $identities,
        private ClockInterface $clock,
    ) {}

    public function __invoke(RegisterRecipientCommand $command): Recipient
    {
        $recipient = Recipient::register($this->identities->nextRecipientId(), $command->name, $this->clock->now());
        $this->recipients->save($recipient);

        return $recipient;
    }
}
