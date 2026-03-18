<?php

namespace Reactolith\SymfonyBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CardFormType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'card_title' => '',
            'card_description' => '',
            'card_footer_text' => null,
            'card_footer_link_label' => null,
            'card_footer_link_url' => null,
            'social_providers' => [],
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['card_title'] = $options['card_title'];
        $view->vars['card_description'] = $options['card_description'];
        $view->vars['card_footer_text'] = $options['card_footer_text'];
        $view->vars['card_footer_link_label'] = $options['card_footer_link_label'];
        $view->vars['card_footer_link_url'] = $options['card_footer_link_url'];
        $view->vars['social_providers'] = $options['social_providers'];
    }

    public function getParent(): string
    {
        return FormType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'reactolith_card';
    }
}
