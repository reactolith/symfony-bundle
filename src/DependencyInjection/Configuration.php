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

        return $treeBuilder;
    }
}
