<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Admin\Menu;

use AlexandreBulete\DddSyliusBundle\Admin\Menu\MenuContributorInterface;
use AlexandreBulete\DddSymfonyBundle\Messenger\Authorization\PermissionCheckerInterface;
use AlexandreBulete\DddSymfonyBundle\Messenger\Tracing\ActorResolverInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Recipients and subscriptions, shown to whoever may list them (ADR 0008), to
 * everyone when nothing checks permissions.
 */
#[AutoconfigureTag('app.menu_contributor', ['priority' => 5])]
final readonly class NotificationMenuContributor implements MenuContributorInterface
{
    public function __construct(
        private ActorResolverInterface $actors,
        private ?PermissionCheckerInterface $checker = null,
    ) {}

    public function contribute(ItemInterface $menu): void
    {
        $root = null;
        foreach ([
            ['notification.find_recipients', 'notification_recipients', 'notification_admin_recipient_index', 'notification.recipient.index'],
            ['notification.find_subscriptions', 'notification_subscriptions', 'notification_admin_subscription_index', 'notification.subscription.index'],
        ] as [$permission, $child, $route, $label]) {
            if (!$this->allowed($permission)) {
                continue;
            }
            $root ??= $menu->addChild('notification')->setLabel('notification.menu.root')->setLabelAttribute('icon', 'tabler:bell');
            $root->addChild($child, ['route' => $route])->setLabel($label);
        }
    }

    private function allowed(string $permission): bool
    {
        if ($this->checker === null) {
            return true;
        }
        $actor = $this->actors->resolve();

        return $actor !== null && $this->checker->isGranted($actor, $permission);
    }
}
