use AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine\Migrations\Version20260930120000;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set(Version20260930120000::class)
        ->autowire()
        ->tag('doctrine_migrations.migration');
};
