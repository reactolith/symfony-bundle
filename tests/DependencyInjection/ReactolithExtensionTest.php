<?php

namespace Reactolith\SymfonyBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\DependencyInjection\ReactolithExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ReactolithExtensionTest extends TestCase
{
    public function testAlias(): void
    {
        $extension = new ReactolithExtension();

        $this->assertSame('reactolith', $extension->getAlias());
    }

    public function testPrependRegistersFormThemeByDefault(): void
    {
        $container = new ContainerBuilder();
        $container->registerExtension($extension = new ReactolithExtension());

        $extension->prepend($container);

        $twigConfigs = $container->getExtensionConfig('twig');
        $formThemes = $this->collectFormThemes($twigConfigs);

        $this->assertContains('@Reactolith/form/reactolith_layout.html.twig', $formThemes);
    }

    public function testPrependSkipsFormThemeWhenDisabled(): void
    {
        $container = new ContainerBuilder();
        $container->registerExtension($extension = new ReactolithExtension());

        // Provide config that disables form_theme
        $container->prependExtensionConfig('reactolith', [
            'form_theme' => ['enabled' => false],
        ]);

        $extension->prepend($container);

        $twigConfigs = $container->getExtensionConfig('twig');
        $formThemes = $this->collectFormThemes($twigConfigs);

        $this->assertNotContains('@Reactolith/form/reactolith_layout.html.twig', $formThemes);
    }

    public function testPrependRegistersTagPrefixAsGlobal(): void
    {
        $container = new ContainerBuilder();
        $container->registerExtension($extension = new ReactolithExtension());

        $extension->prepend($container);

        $twigConfigs = $container->getExtensionConfig('twig');
        $globals = $this->collectGlobals($twigConfigs);

        $this->assertArrayHasKey('reactolith_tag_prefix', $globals);
        $this->assertSame('ui-', $globals['reactolith_tag_prefix']);
    }

    public function testPrependRegistersCustomTagPrefixAsGlobal(): void
    {
        $container = new ContainerBuilder();
        $container->registerExtension($extension = new ReactolithExtension());

        $container->prependExtensionConfig('reactolith', [
            'tag_prefix' => 'x-',
        ]);

        $extension->prepend($container);

        $twigConfigs = $container->getExtensionConfig('twig');
        $globals = $this->collectGlobals($twigConfigs);

        $this->assertSame('x-', $globals['reactolith_tag_prefix']);
    }

    public function testLoadDoesNotThrow(): void
    {
        $extension = new ReactolithExtension();
        $container = new ContainerBuilder();

        $extension->load([], $container);

        // load() is intentionally empty; just ensure it doesn't throw
        $this->assertTrue(true);
    }

    private function collectFormThemes(array $twigConfigs): array
    {
        $themes = [];
        foreach ($twigConfigs as $config) {
            if (isset($config['form_themes'])) {
                $themes = array_merge($themes, $config['form_themes']);
            }
        }

        return $themes;
    }

    private function collectGlobals(array $twigConfigs): array
    {
        $globals = [];
        foreach ($twigConfigs as $config) {
            if (isset($config['globals'])) {
                $globals = array_merge($globals, $config['globals']);
            }
        }

        return $globals;
    }
}
