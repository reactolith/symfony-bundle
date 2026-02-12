<?php

namespace Reactolith\SymfonyBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class ReactolithExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        // Loading is handled by ReactolithBundle::loadExtension()
    }

    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        if ($config['form_theme']['enabled']) {
            $container->prependExtensionConfig('twig', [
                'form_themes' => ['@Reactolith/form/reactolith_layout.html.twig'],
            ]);
        }

        // Register tag_prefix as Twig global
        $container->prependExtensionConfig('twig', [
            'globals' => [
                'reactolith_tag_prefix' => $config['tag_prefix'] ?? 'ui-',
            ],
        ]);
    }

    public function getAlias(): string
    {
        return 'reactolith';
    }
}
