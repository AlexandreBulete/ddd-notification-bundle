<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Resource;

use AlexandreBulete\DddNotificationBundle\Domain\Model\Recipient;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Grid\RecipientGrid;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Processor\CreateRecipientProcessor;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Processor\DeleteRecipientProcessor;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Processor\UpdateRecipientProcessor;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\State\Provider\RecipientItemProvider;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Symfony\Form\RecipientType;
use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\Create;
use Sylius\Resource\Metadata\Delete;
use Sylius\Resource\Metadata\Index;
use Sylius\Resource\Metadata\Update;
use Sylius\Resource\Model\ResourceInterface;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Validator\Constraints as Assert;

#[AsResource(
    alias: 'notification.recipient',
    section: 'admin',
    formType: RecipientType::class,
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    driver: false,
    operations: [
        new Create(processor: CreateRecipientProcessor::class),
        new Update(provider: RecipientItemProvider::class, processor: UpdateRecipientProcessor::class),
        new Delete(provider: RecipientItemProvider::class, processor: DeleteRecipientProcessor::class),
        new Index(grid: RecipientGrid::class),
    ],
)]
final class RecipientResource implements ResourceInterface
{
    public function __construct(
        public ?AbstractUid $id = null,
        #[Assert\NotBlank]
        #[Assert\Length(max: 150)]
        public ?string $name = null,
        public ?string $slack = null,
        public ?string $email = null,
        public ?string $sms = null,
        public ?string $whatsapp = null,
        public ?string $handlesSummary = null,
    ) {}

    public function getId(): ?AbstractUid
    {
        return $this->id;
    }

    public static function fromModel(Recipient $recipient): self
    {
        $on = static fn (Channel $c): ?string => $recipient->addressOn($c);
        $summary = [];
        foreach (Channel::cases() as $c) {
            if ($on($c) !== null) {
                $summary[] = $c->value;
            }
        }

        return new self(
            id: $recipient->id->value(),
            name: $recipient->name,
            slack: $on(Channel::Slack),
            email: $on(Channel::Email),
            sms: $on(Channel::Sms),
            whatsapp: $on(Channel::WhatsApp),
            handlesSummary: $summary === [] ? '—' : implode(', ', $summary),
        );
    }

    /**
     * @return array<string, ?string> channel value => address
     */
    public function handles(): array
    {
        return [
            Channel::Slack->value => $this->slack,
            Channel::Email->value => $this->email,
            Channel::Sms->value => $this->sms,
            Channel::WhatsApp->value => $this->whatsapp,
        ];
    }
}
