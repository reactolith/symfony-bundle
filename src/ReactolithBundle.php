<?php

namespace Reactolith\SymfonyBundle;

use Reactolith\SymfonyBundle\EventListener\ComponentPreloadListener;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class ReactolithBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('tag_prefix')
                    ->defaultValue('ui-')
                    ->cannotBeEmpty()
                    ->info('HTML tag prefix for components (e.g. "ui-" -> <ui-button>, <ui-input>)')
                    ->validate()
                        ->ifTrue(fn ($v) => !is_string($v) || !preg_match('/^[a-z][a-z0-9]*-$/i', $v))
                        ->thenInvalid('The tag prefix must be a non-empty string ending with "-" (e.g. "ui-", "x-"). Got: %s')
                    ->end()
                ->end()
                ->arrayNode('preload')
                    ->canBeEnabled()
                    ->info('Enable HTTP/2 preload headers for Reactolith components')
                ->end()
                ->arrayNode('form_theme')
                    ->canBeDisabled()
                    ->info('Auto-registers the form theme globally')
                ->end()
            ->end()
        ;
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $configs = $builder->getExtensionConfig('reactolith');
        $config = $configs ? array_replace_recursive(...array_reverse($configs)) : [];
        $tagPrefix = $config['tag_prefix'] ?? 'ui-';
        $formThemeEnabled = $config['form_theme']['enabled'] ?? true;

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
