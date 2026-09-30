<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Symfony\Command;

use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use AlexandreBulete\DddNotificationBundle\Application\Command\RegisterRecipient\RegisterRecipientCommand;
use AlexandreBulete\DddNotificationBundle\Application\Command\SetRecipientHandle\SetRecipientHandleCommand;
use AlexandreBulete\DddNotificationBundle\Application\Command\Subscribe\SubscribeCommand;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Recipient;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Topic;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * A recipient, a channel handle and a subscription in one line — enough to
 * wire up notifications before the back office lands.
 */
#[AsCommand(
    name: 'notification:subscribe',
    description: 'Register a recipient with a channel handle and subscribe them to a topic.',
)]
final class SubscribeConsoleCommand extends Command
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'The recipient name.')
            ->addArgument('topic', InputArgument::REQUIRED, 'The topic, e.g. scan.vulnerability_found.')
            ->addArgument('channel', InputArgument::REQUIRED, 'A channel: slack, email, sms or whatsapp.')
            ->addArgument('address', InputArgument::REQUIRED, 'The handle on that channel (Slack channel/user, email, phone).');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $topic = $input->getArgument('topic');
        $channelArg = $input->getArgument('channel');
        $address = $input->getArgument('address');
        if (!is_string($name) || !is_string($topic) || !is_string($channelArg) || !is_string($address)) {
            throw new \InvalidArgumentException('All arguments are strings.');
        }

        $channel = Channel::from($channelArg);

        /** @var Recipient $recipient */
        $recipient = $this->commandBus->dispatch(new RegisterRecipientCommand($name));
        $this->commandBus->dispatch(new SetRecipientHandleCommand($recipient->id, $channel, $address));
        $this->commandBus->dispatch(new SubscribeCommand($recipient->id, new Topic($topic), [$channel]));

        (new SymfonyStyle($input, $output))->success(sprintf('%s subscribed to %s on %s.', $name, $topic, $channel->value));

        return Command::SUCCESS;
    }
}
