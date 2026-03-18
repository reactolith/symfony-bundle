<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Reactolith\SymfonyBundle\Form\Type\CardFormType;
use Reactolith\SymfonyBundle\Form\Type\FieldGroupType;
use Reactolith\SymfonyBundle\Form\Type\SeparatorType;
use Reactolith\SymfonyBundle\Form\Type\SwitchType;
use Reactolith\SymfonyBundle\Twig\ReactolithTwigExtension;

return function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('reactolith.twig_extension', ReactolithTwigExtension::class)
        ->tag('twig.extension');

    if (class_exists(\Symfony\Component\Form\AbstractType::class)) {
        foreach ([
            SwitchType::class,
            CardFormType::class,
            FieldGroupType::class,
            SeparatorType::class,
        ] as $type) {
            $services->set($type)->tag('form.type');
        }
    }
};
