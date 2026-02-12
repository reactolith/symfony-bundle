<?php

namespace Reactolith\SymfonyBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SwitchType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label_attr' => ['class' => ''],
        ]);
    }

    public function getParent(): string
    {
        return CheckboxType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'reactolith_switch';
    }
}
