<?php

declare(strict_types=1);

use AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine\Migrations\Version20260930120000;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine\Migrations\Version20260930160000;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    foreach ([Version20260930120000::class, Version20260930160000::class] as $migration) {
        $services->set($migration)
            ->autowire()
            ->tag('doctrine_migrations.migration');
    }
};