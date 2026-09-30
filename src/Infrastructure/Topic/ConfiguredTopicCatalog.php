<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Topic;

use AlexandreBulete\DddNotificationBundle\Application\Port\TopicCatalogInterface;

/**
 * The topics declared in configuration (`notification.topics`). A host merges
 * one entry per topic it publishes — typically from the bounded context that
 * owns it, so the declaration sits next to the code that raises it.
 */
final readonly class ConfiguredTopicCatalog implements TopicCatalogInterface
{
    /**
     * @param array<string, string> $topics topic value => human label
     */
    public function __construct(private array $topics)
    {
    }

    public function all(): array
    {
        return $this->topics;
    }
}
