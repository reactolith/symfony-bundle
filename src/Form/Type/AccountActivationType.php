<?php

namespace Reactolith\SymfonyBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountActivationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('submit', SubmitType::class, [
            'label' => 'Activate account',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'card_title' => 'Activate your account',
            'card_description' => 'Click the button below to activate your account',
            'login_url' => null,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['login_url']) {
            $view->vars['card_footer_text'] = 'Already activated?';
            $view->vars['card_footer_link_label'] = 'Login';
            $view->vars['card_footer_link_url'] = $options['login_url'];
        }
    }

    public function getParent(): string
    {
        return CardFormType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'reactolith_account_activation';
    }
}
