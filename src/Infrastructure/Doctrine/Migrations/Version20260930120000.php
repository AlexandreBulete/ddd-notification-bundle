<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine\Migrations;

use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Recipients and their subscriptions (ADR 0014). Schema API + DBAL built-in
 * types only — a migration is a snapshot.
 */
final class Version20260930120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Notification: recipients and subscriptions.';
    }

    public function up(Schema $schema): void
    {
        $recipient = $schema->createTable('notification_recipient');
        $recipient->addColumn('id', Types::GUID);
        $recipient->addColumn('name', Types::STRING, ['length' => 150]);
        $recipient->addColumn('handles', Types::JSONB);
        $recipient->addColumn('created_at', Types::DATETIME_IMMUTABLE);
        $recipient->addColumn('updated_at', Types::DATETIME_IMMUTABLE, ['notnull' => false]);
        $recipient->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id')->create());

        $subscription = $schema->createTable('notification_subscription');
        $subscription->addColumn('id', Types::GUID);
        $subscription->addColumn('recipient_id', Types::GUID);
        $subscription->addColumn('topic', Types::STRING, ['length' => 120]);
        $subscription->addColumn('channels', Types::JSONB);
        $subscription->addColumn('created_at', Types::DATETIME_IMMUTABLE);
        $subscription->addColumn('updated_at', Types::DATETIME_IMMUTABLE, ['notnull' => false]);
        $subscription->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id')->create());
        $subscription->addIndex(['topic']);
        $subscription->addIndex(['recipient_id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('notification_subscription');
        $schema->dropTable('notification_recipient');
    }
}
