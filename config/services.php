<?php

declare(strict_types=1);

use AlexandreBulete\DddNotificationBundle\Application\Port\ChannelSenderInterface;
use AlexandreBulete\DddNotificationBundle\Application\Port\DeliveryDispatcherInterface;
use AlexandreBulete\DddNotificationBundle\Application\Port\NotifierInterface;
use AlexandreBulete\DddNotificationBundle\Application\Port\TopicCatalogInterface;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Topic\ConfiguredTopicCatalog;
use AlexandreBulete\DddNotificationBundle\Application\Service\Notifier;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\RecipientRepositoryInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\SubscriptionRepositoryInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Service\IdentityGeneratorInterface;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Channel\EmailChannelSender;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Channel\SlackChannelSender;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine\DoctrineRecipientRepository;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine\DoctrineSubscriptionRepository;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Identity\UlidIdentityGenerator;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Messenger\MessengerDeliveryDispatcher;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Notifier\Bridge\Slack\SlackOptions;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

/**
 * Handlers, queries and the console command register themselves through their
 * attributes; the ports below are the hexagon's seams (ADR 0014).
 */
return static function (ContainerConfigurator $container): void {
    $src = \dirname(__DIR__) . '/src';

    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('iterable $senders', tagged_iterator('notification.channel_sender'));

    $services->instanceof(ChannelSenderInterface::class)->tag('notification.channel_sender');

    $services->load('AlexandreBulete\\DddNotificationBundle\\', $src . '/')
        ->exclude([
            $src . '/Domain',
            $src . '/DddNotificationBundle.php',
            $src . '/Infrastructure/Doctrine/Type',
            $src . '/Infrastructure/Doctrine/Mapping',
            $src . '/Infrastructure/Doctrine/Migrations',
            // Wired conditionally below (its Notifier bridge may be absent).
            $src . '/Infrastructure/Channel',
            // The back office is opt-in — see services_admin.php.
            $src . '/Infrastructure/Sylius',
            $src . '/Infrastructure/Symfony/Form',
        ]);

    // ── Ports → adapters ──────────────────────────────────────────────────────
    $services->alias(RecipientRepositoryInterface::class, DoctrineRecipientRepository::class);
    $services->alias(SubscriptionRepositoryInterface::class, DoctrineSubscriptionRepository::class);
    $services->alias(IdentityGeneratorInterface::class, UlidIdentityGenerator::class);
    $services->alias(NotifierInterface::class, Notifier::class);
    $services->alias(DeliveryDispatcherInterface::class, MessengerDeliveryDispatcher::class);

    $services->set(MessengerDeliveryDispatcher::class)->args([service('command.bus')]);

    // Topics offered by the subscription form, declared by the host.
    $services->set(ConfiguredTopicCatalog::class)->args([param('notification.topics')]);
    $services->alias(TopicCatalogInterface::class, ConfiguredTopicCatalog::class);

    // Channel senders, each only when its transport library is installed.
    if (class_exists(SlackOptions::class)) {
        $services->set(SlackChannelSender::class)->args([service('chatter')]);
    }
    if (interface_exists(MailerInterface::class)) {
        $services->set(EmailChannelSender::class)->args([service(MailerInterface::class), param('notification.mail.from')]);
    }
};
