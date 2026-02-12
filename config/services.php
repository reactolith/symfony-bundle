<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Reactolith\SymfonyBundle\Form\Type\SwitchType;
use Reactolith\SymfonyBundle\Twig\ReactolithTwigExtension;

return function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('reactolith.twig_extension', ReactolithTwigExtension::class)
        ->tag('twig.extension');

    $services->set(SwitchType::class)
        ->tag('form.type');
};
