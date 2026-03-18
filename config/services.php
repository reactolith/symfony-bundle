<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Reactolith\SymfonyBundle\Form\Type\AccountActivationType;
use Reactolith\SymfonyBundle\Form\Type\CardFormType;
use Reactolith\SymfonyBundle\Form\Type\FieldGroupType;
use Reactolith\SymfonyBundle\Form\Type\LoginType;
use Reactolith\SymfonyBundle\Form\Type\PasswordResetConfirmType;
use Reactolith\SymfonyBundle\Form\Type\PasswordResetRequestType;
use Reactolith\SymfonyBundle\Form\Type\SeparatorType;
use Reactolith\SymfonyBundle\Form\Type\SignupType;
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
            LoginType::class,
            SignupType::class,
            PasswordResetRequestType::class,
            PasswordResetConfirmType::class,
            AccountActivationType::class,
        ] as $type) {
            $services->set($type)->tag('form.type');
        }
    }
};
