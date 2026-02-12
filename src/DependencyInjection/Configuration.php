<?php

namespace Reactolith\SymfonyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('reactolith');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('tag_prefix')
                    ->defaultValue('ui-')
                    ->info('HTML tag prefix for components (e.g. "ui-" -> <ui-button>, <ui-input>)')
                ->end()
                ->arrayNode('vite')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Enable Vite asset integration')
                        ->end()
                        ->scalarNode('build_directory')
                            ->defaultValue('build')
                            ->info('Vite build output directory, relative to public/')
                        ->end()
                        ->arrayNode('entry_points')
                            ->scalarPrototype()->end()
                            ->defaultValue(['resources/js/app.js'])
                            ->info('Vite entry point files')
                        ->end()
                        ->scalarNode('dev_server_url')
                            ->defaultNull()
                            ->info('Vite dev server URL (e.g. http://localhost:5173). Leave null for production.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('preload')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                            ->info('Enable HTTP/2 preload headers for Reactolith components')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('form_theme')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Auto-registers the form theme globally')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
