<?php

namespace Reactolith\SymfonyBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordResetRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'm@example.com',
                    'autocomplete' => 'email',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Send reset link',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'card_title' => 'Reset password',
            'card_description' => 'Enter your email address and we will send you a reset link',
            'login_url' => null,
        ]);

        $resolver->setDefault('card_footer_text', function (Options $options) {
            return $options['login_url'] ? 'Remember your password?' : null;
        });

        $resolver->setDefault('card_footer_link_label', function (Options $options) {
            return $options['login_url'] ? 'Back to login' : null;
        });

        $resolver->setDefault('card_footer_link_url', function (Options $options) {
            return $options['login_url'];
        });
    }

    public function getParent(): string
    {
        return CardFormType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'reactolith_password_reset_request';
    }
}
