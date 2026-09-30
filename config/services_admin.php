<?php

declare(strict_types=1);

use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Admin\Menu\NotificationMenuContributor;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Grid\RecipientGrid;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Grid\RecipientGridProvider;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Grid\SubscriptionGrid;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Grid\SubscriptionGridProvider;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Processor\CreateRecipientProcessor;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Processor\CreateSubscriptionProcessor;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Processor\DeleteRecipientProcessor;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Processor\DeleteSubscriptionProcessor;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Processor\UpdateRecipientProcessor;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Processor\UpdateSubscriptionProcessor;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Provider\RecipientItemProvider;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Provider\SubscriptionItemProvider;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Symfony\Form\RecipientType;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Symfony\Form\SubscriptionType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

/**
 * Loaded only when `notification.admin.enabled` is true (the default) —
 * everything Sylius and the forms it drives, nothing else. A headless
 * deployment turns the flag off and manages recipients by console.
 */
return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Forms.
    $services->set(RecipientType::class);
    $services->set(SubscriptionType::class);

    // Recipients.
    $services->set(RecipientGridProvider::class);
    $services->set(RecipientGrid::class)->arg('$limits', param('notification.admin.grid_limits'));
    $services->set(RecipientItemProvider::class);
    $services->set(CreateRecipientProcessor::class);
    $services->set(UpdateRecipientProcessor::class);
    $services->set(DeleteRecipientProcessor::class);

    // Subscriptions.
    $services->set(SubscriptionGridProvider::class);
    $services->set(SubscriptionGrid::class)->arg('$limits', param('notification.admin.grid_limits'));
    $services->set(SubscriptionItemProvider::class);
    $services->set(CreateSubscriptionProcessor::class);
    $services->set(UpdateSubscriptionProcessor::class);
    $services->set(DeleteSubscriptionProcessor::class);

    $services->set(NotificationMenuContributor::class);
};
