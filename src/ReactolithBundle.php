<?php

namespace Reactolith\SymfonyBundle;

use Reactolith\SymfonyBundle\EventListener\ComponentPreloadListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class ReactolithBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        if ($config['preload']['enabled']) {
            $container->services()
                ->set('reactolith.component_preload_listener', ComponentPreloadListener::class)
                ->arg('$tagPrefix', $config['tag_prefix'])
                ->tag('kernel.event_subscriber');
        }
    }
}
