<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Resource\SubscriptionResource;
use Sylius\Bundle\GridBundle\Builder\Action\CreateAction;
use Sylius\Bundle\GridBundle\Builder\Action\DeleteAction;
use Sylius\Bundle\GridBundle\Builder\Action\UpdateAction;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\ItemActionGroup;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\MainActionGroup;
use Sylius\Bundle\GridBundle\Builder\Field\StringField;
use Sylius\Bundle\GridBundle\Builder\Filter\StringFilter;
use Sylius\Component\Grid\Attribute\AsGrid;
use Sylius\Component\Grid\Builder\GridBuilderInterface;

#[AsGrid(resourceClass: SubscriptionResource::class, name: SubscriptionGrid::class, buildMethod: 'buildGrid')]
final class SubscriptionGrid
{
    /** @param list<int> $limits */
    public function __construct(private readonly array $limits) {}

    public function buildGrid(GridBuilderInterface $gridBuilder): void
    {
        $gridBuilder
            ->setProvider(SubscriptionGridProvider::class)
            ->setLimits($this->limits)
            ->orderBy('topic', 'asc')
            ->addFilter(StringFilter::create('topic', ['topic'])->setLabel('notification.subscription.topic'))
            ->addField(StringField::create('topic')->setLabel('notification.subscription.topic')->setSortable(true))
            ->addField(StringField::create('recipientName')->setLabel('notification.subscription.recipient'))
            ->addField(StringField::create('channelsSummary')->setLabel('notification.subscription.channels'))
            ->addActionGroup(MainActionGroup::create(CreateAction::create()))
            ->addActionGroup(ItemActionGroup::create(UpdateAction::create(), DeleteAction::create()))
        ;
    }
}
