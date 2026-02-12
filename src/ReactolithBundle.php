<?php

namespace Reactolith\SymfonyBundle;

use Reactolith\SymfonyBundle\EventListener\ComponentPreloadListener;
use Reactolith\SymfonyBundle\Vite\ViteAssetResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class ReactolithBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        $services = $container->services();

        // Configure Twig extension with resolved config
        $services->get('reactolith.twig_extension')
            ->arg('$config', $config);

        // Vite integration
        if ($config['vite']['enabled']) {
            $services->set('reactolith.vite_resolver', ViteAssetResolver::class)
                ->arg('$projectDir', '%kernel.project_dir%')
                ->arg('$config', $config['vite']);

            $services->get('reactolith.twig_extension')
                ->arg('$viteResolver', service('reactolith.vite_resolver'));
        }

        // Component preload listener
        if ($config['preload']['enabled']) {
            $def = $services->set('reactolith.component_preload_listener', ComponentPreloadListener::class)
                ->arg('$tagPrefix', $config['tag_prefix'])
                ->tag('kernel.event_subscriber');

            if ($config['vite']['enabled']) {
                $def->arg('$viteResolver', service('reactolith.vite_resolver'));
            }
        }
    }
}
