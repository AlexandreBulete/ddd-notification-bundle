<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Channel;

use AlexandreBulete\DddNotificationBundle\Application\Delivery\PendingDelivery;
use AlexandreBulete\DddNotificationBundle\Application\Port\ChannelSenderInterface;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;
use Symfony\Component\Notifier\Bridge\Slack\SlackOptions;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;

/**
 * Delivers on Slack through Symfony Notifier (ADR 0014). The recipient's
 * handle is the Slack channel or user id to route to; the message goes out on
 * the `slack` transport the project configured.
 *
 * Wired only when the Slack bridge (symfony/slack-notifier) is installed —
 * a project that wants only email never pulls Slack in.
 */
final readonly class SlackChannelSender implements ChannelSenderInterface
{
    public function __construct(
        private ChatterInterface $chatter,
    ) {}

    public function supports(Channel $channel): bool
    {
        return $channel === Channel::Slack;
    }

    public function send(PendingDelivery $delivery): void
    {
        $message = (new ChatMessage($delivery->subject . "\n" . $delivery->body))
            ->transport('slack')
            ->options((new SlackOptions())->recipient($delivery->address));

        $this->chatter->send($message);
    }
}
