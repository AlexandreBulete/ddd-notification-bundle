use AlexandreBulete\DddNotificationBundle\Application\Port\ChannelSenderInterface;
use AlexandreBulete\DddNotificationBundle\Application\Port\DeliveryDispatcherInterface;
use AlexandreBulete\DddNotificationBundle\Application\Port\NotifierInterface;
use AlexandreBulete\DddNotificationBundle\Application\Service\Notifier;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\RecipientRepositoryInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\SubscriptionRepositoryInterface;
use AlexandreBulete\DddNotificationBundle\Domain\Service\IdentityGeneratorInterface;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Channel\SlackChannelSender;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine\DoctrineRecipientRepository;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine\DoctrineSubscriptionRepository;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Identity\UlidIdentityGenerator;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Messenger\DeliverNotificationHandler;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Messenger\MessengerDeliveryDispatcher;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Notifier\Bridge\Slack\SlackOptions;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('iterable $senders', tagged_iterator('notification.channel_sender'));

    $services->instanceof(ChannelSenderInterface::class)->tag('notification.channel_sender');

    // Read side + repos
    $services->set(DoctrineRecipientRepository::class);
    $services->set(DoctrineSubscriptionRepository::class);
    $services->alias(RecipientRepositoryInterface::class, DoctrineRecipientRepository::class);
    $services->alias(SubscriptionRepositoryInterface::class, DoctrineSubscriptionRepository::class);
    $services->set(UlidIdentityGenerator::class);
    $services->alias(IdentityGeneratorInterface::class, UlidIdentityGenerator::class);

    // The notifier + async delivery
    $services->set(Notifier::class);
    $services->alias(NotifierInterface::class, Notifier::class);
    $services->set(MessengerDeliveryDispatcher::class)->args([service('command.bus')]);
    $services->alias(DeliveryDispatcherInterface::class, MessengerDeliveryDispatcher::class);
    $services->set(DeliverNotificationHandler::class);

    // Channel senders, each only when its Notifier bridge is installed.
    if (class_exists(SlackOptions::class)) {
        $services->set(SlackChannelSender::class)->args([service('chatter')]);
    }
};
