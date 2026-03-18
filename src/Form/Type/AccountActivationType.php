<?php

namespace Reactolith\SymfonyBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
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

        $resolver->setDefault('card_footer_text', function (Options $options) {
            return $options['login_url'] ? 'Already activated?' : null;
        });

        $resolver->setDefault('card_footer_link_label', function (Options $options) {
            return $options['login_url'] ? 'Login' : null;
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
        return 'reactolith_account_activation';
    }
}
