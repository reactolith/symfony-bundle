<?php

namespace Reactolith\SymfonyBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeparatorType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => false,
            'label' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'reactolith_separator';
    }
}
