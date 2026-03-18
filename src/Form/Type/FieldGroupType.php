<?php

namespace Reactolith\SymfonyBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FieldGroupType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'inherit_data' => true,
            'row' => false,
            'gap' => '4',
        ]);

        $resolver->setAllowedValues('gap', ['0', '1', '2', '3', '4', '5', '6', '8', '10', '12']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['row'] = $options['row'];
        $view->vars['gap'] = $options['gap'];
    }

    public function getBlockPrefix(): string
    {
        return 'reactolith_field_group';
    }
}
