<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * A recipient subscribes to a topic at most once (ADR 0014): enforce it in the
 * schema. Expand-only — a unique index on a pair that was already effectively
 * unique through the write path.
 */
final class Version20260930160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Notification: one subscription per (recipient, topic).';
    }

    public function up(Schema $schema): void
    {
        $schema->getTable('notification_subscription')
            ->addUniqueIndex(['recipient_id', 'topic'], 'uniq_notification_subscription_recipient_topic');
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('notification_subscription')
            ->dropIndex('uniq_notification_subscription_recipient_topic');
    }
}
