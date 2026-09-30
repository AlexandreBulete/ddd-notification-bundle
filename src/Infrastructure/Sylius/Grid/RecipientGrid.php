<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Resource\RecipientResource;
use Sylius\Bundle\GridBundle\Builder\Action\CreateAction;
use Sylius\Bundle\GridBundle\Builder\Action\DeleteAction;
use Sylius\Bundle\GridBundle\Builder\Action\UpdateAction;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\ItemActionGroup;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\MainActionGroup;
use Sylius\Bundle\GridBundle\Builder\Field\StringField;
use Sylius\Bundle\GridBundle\Builder\Filter\StringFilter;
use Sylius\Component\Grid\Attribute\AsGrid;
use Sylius\Component\Grid\Builder\GridBuilderInterface;

#[AsGrid(resourceClass: RecipientResource::class, name: RecipientGrid::class, buildMethod: 'buildGrid')]
final class RecipientGrid
{
    /** @param list<int> $limits */
    public function __construct(private readonly array $limits) {}

    public function buildGrid(GridBuilderInterface $gridBuilder): void
    {
        $gridBuilder
            ->setProvider(RecipientGridProvider::class)
            ->setLimits($this->limits)
            ->orderBy('name', 'asc')
            ->addFilter(StringFilter::create('name', ['name'])->setLabel('notification.recipient.name'))
            ->addField(StringField::create('name')->setLabel('notification.recipient.name')->setSortable(true))
            ->addField(StringField::create('handlesSummary')->setLabel('notification.recipient.channels'))
            ->addActionGroup(MainActionGroup::create(CreateAction::create()))
            ->addActionGroup(ItemActionGroup::create(UpdateAction::create(), DeleteAction::create()))
        ;
    }
}
