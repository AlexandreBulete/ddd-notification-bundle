<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Resource;

use AlexandreBulete\DddNotificationBundle\Domain\Model\Subscription;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Grid\SubscriptionGrid;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Processor\CreateSubscriptionProcessor;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Processor\DeleteSubscriptionProcessor;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Processor\UpdateSubscriptionProcessor;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Provider\SubscriptionItemProvider;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Symfony\Form\SubscriptionType;
use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\Create;
use Sylius\Resource\Metadata\Delete;
use Sylius\Resource\Metadata\Index;
use Sylius\Resource\Metadata\Update;
use Sylius\Resource\Model\ResourceInterface;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Validator\Constraints as Assert;

#[AsResource(
    alias: 'notification.subscription',
    section: 'admin',
    formType: SubscriptionType::class,
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    driver: false,
    operations: [
        new Create(processor: CreateSubscriptionProcessor::class),
        new Update(provider: SubscriptionItemProvider::class, processor: UpdateSubscriptionProcessor::class),
        new Delete(provider: SubscriptionItemProvider::class, processor: DeleteSubscriptionProcessor::class),
        new Index(grid: SubscriptionGrid::class),
    ],
)]
final class SubscriptionResource implements ResourceInterface
{
    /**
     * @param list<string> $topics   topic values, chosen on creation (one row per topic)
     * @param list<string> $channels channel values
     */
    public function __construct(
        public ?AbstractUid $id = null,
        // Set on creation only.
        #[Assert\NotBlank(groups: ['create'])]
        public ?string $recipientId = null,
        public ?string $recipientName = null,
        // Chosen on creation (possibly several at once); carried read-only on
        // edit, where only the channels change — hence no constraint here.
        #[Assert\All([new Assert\Regex('/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)+$/', message: 'notification.subscription.topic_format')])]
        #[Assert\Count(min: 1, minMessage: 'notification.subscription.topics_required', groups: ['create'])]
        public array $topics = [],
        public ?string $topic = null,
        #[Assert\Count(min: 1, minMessage: 'notification.subscription.channels_required')]
        public array $channels = [],
        public ?string $channelsSummary = null,
    ) {}

    public function getId(): ?AbstractUid
    {
        return $this->id;
    }

    public static function fromModel(Subscription $subscription, string $recipientName): self
    {
        $channels = array_map(static fn (Channel $c): string => $c->value, $subscription->channels);

        return new self(
            id: $subscription->id->value(),
            recipientId: (string) $subscription->recipientId,
            recipientName: $recipientName,
            topic: $subscription->topic->value(),
            channels: $channels,
            channelsSummary: $channels === [] ? '—' : implode(', ', $channels),
        );
    }
}
