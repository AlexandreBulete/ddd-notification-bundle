<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Channel;

use AlexandreBulete\DddNotificationBundle\Application\Delivery\PendingDelivery;
use AlexandreBulete\DddNotificationBundle\Application\Port\ChannelSenderInterface;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Delivers on email through Symfony Mailer. The recipient's handle is the
 * destination address; the sender address is configured by the host
 * (`notification.mail.from`). Wired only when Symfony Mailer is installed and a
 * from address is set — otherwise the email channel is simply unsupported and
 * such deliveries are skipped, not errored.
 */
final readonly class EmailChannelSender implements ChannelSenderInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private string $from,
    ) {
    }

    public function supports(Channel $channel): bool
    {
        return Channel::Email === $channel && '' !== $this->from;
    }

    public function send(PendingDelivery $delivery): void
    {
        $email = (new Email())
            ->from($this->from)
            ->to($delivery->address)
            ->subject($delivery->subject)
            ->text($delivery->body);

        $this->mailer->send($email);
    }
}
