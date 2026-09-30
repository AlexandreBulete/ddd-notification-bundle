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
 *
 * @phpstan-type NotificationConfig array{
 *     topics: array<string, string>,
 *     admin: array{enabled: bool, grid_limits: list<int>},
 * }
 */
final class DddNotificationBundle extends AbstractBundle
{
    protected string $extensionAlias = 'notification';

    public function configure(DefinitionConfigurator $definition): void
    {
        // Channels come from Symfony Notifier transports; recipients and
        // subscriptions are data. The only seam is the back office, which a
        // headless or API-only deployment turns off.
        $definition->rootNode()
            ->children()
                ->arrayNode('topics')
                    ->info('Topics a host publishes on, value => label, offered as a choice in the subscription form. Each bounded context declares the topic it raises.')
                    ->useAttributeAsKey('name')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('admin')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Sylius back office for recipients and subscriptions. Turn off to manage them by console only.')
                        ->end()
                        ->arrayNode('grid_limits')
                            ->integerPrototype()->end()
                            ->defaultValue([10, 25, 50])
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array<mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        /** @var NotificationConfig $config */
        $container->parameters()
            ->set('notification.topics', $config['topics'])
            ->set('notification.admin.grid_limits', $config['admin']['grid_limits']);

        $container->import($this->getPath() . '/config/services.php');

        if ($config['admin']['enabled']) {
            $container->import($this->getPath() . '/config/services_admin.php');
        }

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
            'translator' => ['paths' => [$this->getPath() . '/translations']],
            'messenger' => ['routing' => [
                \AlexandreBulete\DddNotificationBundle\Infrastructure\Messenger\DeliverNotification::class => 'async',
            ]],
        ]);

        if ($this->adminEnabled($builder)) {
            // DddSyliusBundle only globs the application's own
            // src/*/Infrastructure/Sylius/Resource; a bundle declares its own.
            $builder->prependExtensionConfig('sylius_resource', [
                'mapping' => ['paths' => [$this->getPath() . '/src/Infrastructure/Sylius/Resource']],
            ]);
        }
    }

    /**
     * prependExtension() runs before the config tree is processed, so the
     * default (on) needs its own fallback here.
     */
    private function adminEnabled(ContainerBuilder $builder): bool
    {
        /** @var list<array<string, mixed>> $configs */
        $configs = $builder->getExtensionConfig($this->extensionAlias);
        $enabled = true;
        foreach ($configs as $config) {
            $admin = $config['admin'] ?? null;
            if (is_array($admin) && is_bool($admin['enabled'] ?? null)) {
                $enabled = $admin['enabled'];
            }
        }

        return $enabled;
    }

    private static function migrationsEnabled(ContainerBuilder $builder): bool
    {
        /** @var array<string, class-string> $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        return in_array(DoctrineMigrationsBundle::class, $bundles, true);
    }
}
