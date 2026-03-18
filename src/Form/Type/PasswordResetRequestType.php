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
                'label' => $options['label_email'],
                'attr' => [
                    'placeholder' => $options['placeholder_email'],
                    'autocomplete' => 'email',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['label_submit'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'card_title' => 'Reset password',
            'card_description' => 'Enter your email address and we will send you a reset link',
            'card_footer_text' => 'Remember your password?',
            'card_footer_link_label' => 'Back to login',
            'label_email' => 'Email',
            'label_submit' => 'Send reset link',
            'placeholder_email' => 'm@example.com',
            'login_url' => null,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['login_url']) {
            $view->vars['card_footer_text'] = $options['card_footer_text'];
            $view->vars['card_footer_link_label'] = $options['card_footer_link_label'];
            $view->vars['card_footer_link_url'] = $options['login_url'];
        } else {
            $view->vars['card_footer_text'] = null;
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
