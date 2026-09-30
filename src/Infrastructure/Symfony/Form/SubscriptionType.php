<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Symfony\Form;

use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
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
    public function __construct(private readonly QueryBusInterface $queryBus) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $resource = $options['data'] ?? null;
        $isCreation = !$resource instanceof SubscriptionResource || $resource->id === null;

        if ($isCreation) {
            $builder->add('recipientId', ChoiceType::class, [
                'label' => 'notification.subscription.recipient',
                'choices' => $this->recipientChoices(),
                'choice_translation_domain' => false,
            ]);
            $builder->add('topic', TextType::class, ['label' => 'notification.subscription.topic', 'help' => 'notification.subscription.topic_help']);
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
