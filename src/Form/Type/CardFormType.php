<?php

namespace Reactolith\SymfonyBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
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
            'translation_domain' => 'reactolith',
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        foreach (['card_title', 'card_description', 'card_footer_text', 'card_footer_link_label', 'card_footer_link_url', 'social_providers', 'translation_domain'] as $key) {
            $view->vars[$key] = $options[$key];
        }
    }

    /**
     * Configure footer link defaults that derive from a URL option.
     *
     * Reduces boilerplate in child types that need a card footer link
     * derived from a convenience URL option (e.g. signup_url, login_url).
     */
    public static function configureFooterLink(
        OptionsResolver $resolver,
        string $urlOption,
        string $footerText,
        string $linkLabel,
    ): void {
        $resolver->setDefault('card_footer_text', function (Options $options) use ($urlOption, $footerText) {
            return $options[$urlOption] ? $footerText : null;
        });

        $resolver->setDefault('card_footer_link_label', function (Options $options) use ($urlOption, $linkLabel) {
            return $options[$urlOption] ? $linkLabel : null;
        });

        $resolver->setDefault('card_footer_link_url', function (Options $options) use ($urlOption) {
            return $options[$urlOption];
        });
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
