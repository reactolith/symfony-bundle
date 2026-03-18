<?php

namespace Reactolith\SymfonyBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordResetConfirmType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => $options['label_password']],
                'second_options' => ['label' => $options['label_password_confirm']],
                'options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['label_submit'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'card_title' => 'Set new password',
            'card_description' => 'Enter your new password below',
            'label_password' => 'New password',
            'label_password_confirm' => 'Confirm new password',
            'label_submit' => 'Reset password',
        ]);
    }

    public function getParent(): string
    {
        return CardFormType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'reactolith_password_reset_confirm';
    }
}
