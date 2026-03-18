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
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'm@example.com',
                    'autocomplete' => 'email',
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'attr' => [
                    'autocomplete' => 'current-password',
                ],
            ]);

        if ($options['remember_me']) {
            $builder->add('remember_me', CheckboxType::class, [
                'label' => 'Remember me',
                'required' => false,
                'mapped' => false,
            ]);
        }

        $builder->add('submit', SubmitType::class, [
            'label' => 'Login',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'card_title' => 'Login',
            'card_description' => 'Enter your email below to login to your account',
            'forgot_password_url' => null,
            'signup_url' => null,
            'remember_me' => false,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['signup_url']) {
            $view->vars['card_footer_text'] = "Don't have an account?";
            $view->vars['card_footer_link_label'] = 'Sign up';
            $view->vars['card_footer_link_url'] = $options['signup_url'];
        }
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['forgot_password_url'] && isset($view['password'])) {
            $view['password']->vars['label_link_url'] = $options['forgot_password_url'];
            $view['password']->vars['label_link_label'] = 'Forgot your password?';
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
