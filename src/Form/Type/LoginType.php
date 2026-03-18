<?php

namespace Reactolith\SymfonyBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginType extends AbstractType
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
            ->add('password', PasswordType::class, [
                'label' => $options['label_password'],
                'attr' => [
                    'autocomplete' => 'current-password',
                ],
            ]);

        if ($options['remember_me']) {
            $builder->add('remember_me', CheckboxType::class, [
                'label' => $options['label_remember_me'],
                'required' => false,
                'mapped' => false,
            ]);
        }

        $builder->add('submit', SubmitType::class, [
            'label' => $options['label_submit'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'card_title' => 'Login',
            'card_description' => 'Enter your email below to login to your account',
            'card_footer_text' => "Don't have an account?",
            'card_footer_link_label' => 'Sign up',
            'label_email' => 'Email',
            'label_password' => 'Password',
            'label_submit' => 'Login',
            'label_remember_me' => 'Remember me',
            'label_forgot_password' => 'Forgot your password?',
            'placeholder_email' => 'm@example.com',
            'forgot_password_url' => null,
            'signup_url' => null,
            'remember_me' => false,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['signup_url']) {
            $view->vars['card_footer_text'] = $options['card_footer_text'];
            $view->vars['card_footer_link_label'] = $options['card_footer_link_label'];
            $view->vars['card_footer_link_url'] = $options['signup_url'];
        } else {
            $view->vars['card_footer_text'] = null;
        }
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['forgot_password_url'] && isset($view['password'])) {
            $view['password']->vars['label_link_url'] = $options['forgot_password_url'];
            $view['password']->vars['label_link_label'] = $options['label_forgot_password'];
        }
    }

    public function getParent(): string
    {
        return CardFormType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'reactolith_login';
    }
}
