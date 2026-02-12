<?php

namespace Reactolith\SymfonyBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\ReactolithBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ReactolithBundlePrependTest extends TestCase
{
    public function testPrependRegistersFormThemeByDefault(): void
    {
        $builder = $this->createPrependedContainer([]);

        $twigConfigs = $builder->getExtensionConfig('twig');
        $formThemes = $this->collectFormThemes($twigConfigs);

        $this->assertContains('@Reactolith/form/reactolith_layout.html.twig', $formThemes);
    }

    public function testPrependSkipsFormThemeWhenDisabled(): void
    {
        $builder = $this->createPrependedContainer([
            'form_theme' => ['enabled' => false],
        ]);

        $twigConfigs = $builder->getExtensionConfig('twig');
        $formThemes = $this->collectFormThemes($twigConfigs);

        $this->assertNotContains('@Reactolith/form/reactolith_layout.html.twig', $formThemes);
    }

    public function testPrependRegistersDefaultTagPrefixAsGlobal(): void
    {
        $builder = $this->createPrependedContainer([]);

        $twigConfigs = $builder->getExtensionConfig('twig');
        $globals = $this->collectGlobals($twigConfigs);

        $this->assertArrayHasKey('reactolith_tag_prefix', $globals);
        $this->assertSame('ui-', $globals['reactolith_tag_prefix']);
    }

    public function testPrependRegistersCustomTagPrefixAsGlobal(): void
    {
        $builder = $this->createPrependedContainer([
            'tag_prefix' => 'x-',
        ]);

        $twigConfigs = $builder->getExtensionConfig('twig');
        $globals = $this->collectGlobals($twigConfigs);

        $this->assertSame('x-', $globals['reactolith_tag_prefix']);
    }

    private function createPrependedContainer(array $bundleConfig): ContainerBuilder
    {
        $bundle = new ReactolithBundle();
        $builder = new ContainerBuilder();

        if ($bundleConfig !== []) {
            $builder->prependExtensionConfig('reactolith', $bundleConfig);
        }

        // AbstractBundle::prependExtension() needs a ContainerConfigurator.
        // We call the prepend logic via reflection since we can't easily
        // construct a full ContainerConfigurator in tests.
        $method = new \ReflectionMethod($bundle, 'prependExtension');

        $loader = new \Symfony\Component\DependencyInjection\Loader\PhpFileLoader(
            $builder,
            new \Symfony\Component\Config\FileLocator(),
        );
        $instanceof = [];
        $configurator = new \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator(
            $builder,
            $loader,
            $instanceof,
            __DIR__,
            'test',
        );

        $method->invoke($bundle, $configurator, $builder);

        return $builder;
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
