<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Application\Port;

/**
 * The topics a host publishes on, so the back office can offer them as a
 * choice instead of free text. Standalone the catalogue is empty (the form
 * falls back to a text field); each host declares its topics through
 * configuration (`notification.topics`) or its own implementation.
 */
interface TopicCatalogInterface
{
    /**
     * @return array<string, string> topic value => human label
     */
    public function all(): array;
}
