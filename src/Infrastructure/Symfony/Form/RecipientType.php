<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Symfony\Form;

use AlexandreBulete\DddNotificationBundle\Infrastructure\Sylius\Resource\RecipientResource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RecipientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'notification.recipient.name'])
            ->add('slack', TextType::class, ['label' => 'notification.recipient.slack', 'required' => false, 'help' => 'notification.recipient.slack_help'])
            ->add('email', TextType::class, ['label' => 'notification.recipient.email', 'required' => false])
            ->add('sms', TextType::class, ['label' => 'notification.recipient.sms', 'required' => false, 'help' => 'notification.recipient.phone_help'])
            ->add('whatsapp', TextType::class, ['label' => 'notification.recipient.whatsapp', 'required' => false, 'help' => 'notification.recipient.phone_help'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => RecipientResource::class]);
    }
}
