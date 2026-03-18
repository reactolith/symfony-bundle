<?php

namespace Reactolith\SymfonyBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
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
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['login_url']) {
            $view->vars['card_footer_text'] = 'Remember your password?';
            $view->vars['card_footer_link_label'] = 'Back to login';
            $view->vars['card_footer_link_url'] = $options['login_url'];
        }
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
