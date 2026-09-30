<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle;

use AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine\Type\ChannelsType;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine\Type\HandlesType;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine\Type\RecipientIdType;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine\Type\SubscriptionIdType;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine\Type\TopicType;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Notify people where they want it (ADR 0014): one topic, many recipients,
 * each on their own channel — built on Symfony Notifier, delivered
 * asynchronously. Standalone: no IAM, no project notion.
 */
final class DddNotificationBundle extends AbstractBundle
{
    protected string $extensionAlias = 'notification';

    public function configure(DefinitionConfigurator $definition): void
    {
        // No configuration for now: channels come from Symfony Notifier
        // transports, recipients and subscriptions are data.
        $definition->rootNode();
    }

    /**
     * @param array<mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import($this->getPath() . '/config/services.php');

        if (self::migrationsEnabled($builder)) {
            $container->import($this->getPath() . '/config/services_migrations.php');
        }
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->prependExtensionConfig('doctrine', [
            'dbal' => [
                'types' => [
                    RecipientIdType::NAME => RecipientIdType::class,
                    SubscriptionIdType::NAME => SubscriptionIdType::class,
                    TopicType::NAME => TopicType::class,
                    HandlesType::NAME => HandlesType::class,
                    ChannelsType::NAME => ChannelsType::class,
                ],
            ],
            'orm' => [
                'mappings' => [
                    'Notification' => [
                        'type' => 'xml',
                        'is_bundle' => false,
                        'dir' => $this->getPath() . '/src/Infrastructure/Doctrine/Mapping',
                        'prefix' => 'AlexandreBulete\DddNotificationBundle\Domain\Model',
                        'alias' => 'Notification',
                    ],
                ],
            ],
        ]);
        if (self::migrationsEnabled($builder)) {
            $builder->prependExtensionConfig('doctrine_migrations', ['enable_service_migrations' => true]);
        }
        $builder->prependExtensionConfig('framework', [
            'messenger' => ['routing' => [
                \AlexandreBulete\DddNotificationBundle\Infrastructure\Messenger\DeliverNotification::class => 'async',
            ]],
            'translator' => ['paths' => [$this->getPath() . '/translations']],
        ]);
    }

    private static function migrationsEnabled(ContainerBuilder $builder): bool
    {
        /** @var array<string, class-string> $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        return in_array(DoctrineMigrationsBundle::class, $bundles, true);
    }

}
