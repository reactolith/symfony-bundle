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
                ->scalarNode('root_selector')
                    ->defaultValue('#reactolith')
                    ->info('CSS selector for the Reactolith root element')
                ->end()
                ->scalarNode('tag_prefix')
                    ->defaultValue('ui-')
                    ->info('HTML tag prefix for components (e.g. "ui-" -> <ui-button>, <ui-input>)')
                ->end()
                ->arrayNode('mercure')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Set to false to disable Mercure integration')
                        ->end()
                        ->scalarNode('hub_url')
                            ->defaultNull()
                            ->info('Auto-detected from symfony/mercure-bundle, or set manually')
                        ->end()
                        ->booleanNode('with_credentials')
                            ->defaultFalse()
                            ->info('Whether to send cookies with Mercure requests')
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
