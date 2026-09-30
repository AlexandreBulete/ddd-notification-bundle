<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Domain\ValueObject;

use AlexandreBulete\DddFoundation\Domain\ValueObject\StringVO;

/**
 * What a notification is about, in dotted snake_case
 * (`scan.vulnerability_found`, `mission.deployment_done`). The code that
 * notifies names its topic; recipients subscribe to topics.
 */
final readonly class Topic extends StringVO
{
    protected function validate(string $value): void
    {
        if (preg_match('/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)+$/', $value) !== 1) {
            throw new \InvalidArgumentException(sprintf('Invalid topic "%s": expected dotted snake_case, like "scan.vulnerability_found".', $value));
        }
    }
}
