<?php

namespace Reactolith\SymfonyBundle;

use Reactolith\SymfonyBundle\EventListener\ComponentPreloadListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class ReactolithBundle extends AbstractBundle
{
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $config = $builder->getExtensionConfig('reactolith');
        $tagPrefix = $config[0]['tag_prefix'] ?? 'ui-';
        $formThemeEnabled = $config[0]['form_theme']['enabled'] ?? true;

        if ($formThemeEnabled) {
            $builder->prependExtensionConfig('twig', [
                'form_themes' => ['@Reactolith/form/reactolith_layout.html.twig'],
            ]);
        }

        $builder->prependExtensionConfig('twig', [
            'globals' => [
                'reactolith_tag_prefix' => $tagPrefix,
            ],
        ]);
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        if ($config['preload']['enabled']) {
            $container->services()
                ->set('reactolith.component_preload_listener', ComponentPreloadListener::class)
                ->arg('$tagPrefix', $config['tag_prefix'])
                ->tag('kernel.event_subscriber');
        }
    }
}
