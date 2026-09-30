<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Symfony\Form;

use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use AlexandreBulete\DddNotificationBundle\Application\Port\TopicCatalogInterface;
use AlexandreBulete\DddNotificationBundle\Application\Query\FindRecipients\FindRecipientsQuery;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Resource\SubscriptionResource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SubscriptionType extends AbstractType
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly TopicCatalogInterface $topics,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $resource = $options['data'] ?? null;
        $isCreation = !$resource instanceof SubscriptionResource || $resource->id === null;

        if ($isCreation) {
            // One form creates one subscription per topic picked — a recipient's
            // topics set in a single step. Topics come from the host catalogue;
            // with none declared, subscriptions are created by console.
            $builder->add('recipientId', ChoiceType::class, [
                'label' => 'notification.subscription.recipient',
                'choices' => $this->recipientChoices(),
                'choice_translation_domain' => false,
            ]);
            $builder->add('topics', ChoiceType::class, [
                'label' => 'notification.subscription.topics',
                'help' => 'notification.subscription.topics_help',
                'choices' => array_flip($this->topics->all()),
                'choice_translation_domain' => false,
                'multiple' => true,
                'expanded' => true,
            ]);
        } else {
            // On edit the topic is fixed; show it, change only the channels.
            $builder->add('topic', TextType::class, [
                'label' => 'notification.subscription.topic',
                'disabled' => true,
            ]);
        }

        $channels = [];
        foreach (Channel::cases() as $channel) {
            $channels['notification.channel.' . $channel->value] = $channel->value;
        }
        $builder->add('channels', ChoiceType::class, [
            'label' => 'notification.subscription.channels',
            'choices' => $channels,
            'multiple' => true,
            'expanded' => true,
        ]);
    }

    /**
     * @return array<string, string> recipient name => id
     */
    private function recipientChoices(): array
    {
        $choices = [];
        foreach ($this->queryBus->ask(new FindRecipientsQuery()) as $recipient) {
            $choices[$recipient->name] = (string) $recipient->id;
        }

        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => SubscriptionResource::class]);
    }
}
