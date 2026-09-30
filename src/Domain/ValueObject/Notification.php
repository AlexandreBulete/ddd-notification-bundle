<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Domain\ValueObject;

/**
 * What to tell people: a topic, a subject line and a body. Channel-agnostic —
 * each channel renders it its own way.
 */
final readonly class Notification
{
    public function __construct(
        public Topic $topic,
        public string $subject,
        public string $body,
    ) {
        if (trim($subject) === '') {
            throw new \InvalidArgumentException('A notification needs a subject.');
        }
    }
}
