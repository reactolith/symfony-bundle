<?php

namespace Reactolith\SymfonyBundle\Tests;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\EventListener\ComponentPreloadListener;
use Reactolith\SymfonyBundle\ReactolithBundle;
use Reactolith\SymfonyBundle\Twig\ReactolithTwigExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ReactolithBundleTest extends TestCase
{
    public function testLoadExtensionRegistersCoreServices(): void
    {
        $container = $this->loadBundle([
            'tag_prefix' => 'ui-',
            'preload' => ['enabled' => false],
            'form_theme' => ['enabled' => true],
        ]);

        $this->assertTrue($container->has('reactolith.twig_extension'));

        $twigDef = $container->getDefinition('reactolith.twig_extension');
        $this->assertSame(ReactolithTwigExtension::class, $twigDef->getClass());
    }

    public function testLoadExtensionRegistersPreloadListenerWhenEnabled(): void
    {
        $container = $this->loadBundle([
            'tag_prefix' => 'ui-',
            'preload' => ['enabled' => true],
            'form_theme' => ['enabled' => true],
        ]);

        $this->assertTrue($container->has('reactolith.component_preload_listener'));

        $def = $container->getDefinition('reactolith.component_preload_listener');
        $this->assertSame(ComponentPreloadListener::class, $def->getClass());
        $this->assertTrue($def->hasTag('kernel.event_subscriber'));
    }

    public function testLoadExtensionDoesNotRegisterPreloadListenerWhenDisabled(): void
    {
        $container = $this->loadBundle([
            'tag_prefix' => 'ui-',
            'preload' => ['enabled' => false],
            'form_theme' => ['enabled' => true],
        ]);

        $this->assertFalse($container->has('reactolith.component_preload_listener'));
    }

    public function testPreloadListenerReceivesTagPrefix(): void
    {
        $container = $this->loadBundle([
            'tag_prefix' => 'x-',
            'preload' => ['enabled' => true],
            'form_theme' => ['enabled' => true],
        ]);

        $def = $container->getDefinition('reactolith.component_preload_listener');
        $this->assertSame('x-', $def->getArgument('$tagPrefix'));
    }

    public function testSwitchTypeIsRegistered(): void
    {
        $container = $this->loadBundle([
            'tag_prefix' => 'ui-',
            'preload' => ['enabled' => false],
            'form_theme' => ['enabled' => true],
        ]);

        $this->assertTrue($container->has(\Reactolith\SymfonyBundle\Form\Type\SwitchType::class));

        $def = $container->getDefinition(\Reactolith\SymfonyBundle\Form\Type\SwitchType::class);
        $this->assertTrue($def->hasTag('form.type'));
    }

    private function loadBundle(array $config): ContainerBuilder
    {
        $bundle = new ReactolithBundle();
        $builder = new ContainerBuilder();

        // The PhpFileLoader needs a YAML resolver to handle the services.yaml import
        $bundleRoot = dirname(__DIR__);
        $locator = new FileLocator($bundleRoot . '/config');
        $phpLoader = new PhpFileLoader($builder, $locator);

        // Register the YAML loader so that $container->import('../config/services.yaml') works
        $yamlLoader = new YamlFileLoader($builder, $locator);
        $resolver = new \Symfony\Component\Config\Loader\LoaderResolver([$yamlLoader, $phpLoader]);
        $phpLoader->setResolver($resolver);

        $instanceof = [];
        $configurator = new ContainerConfigurator(
            $builder,
            $phpLoader,
            $instanceof,
            $bundleRoot . '/src/ReactolithBundle.php',
            'ReactolithBundle.php',
        );

        $bundle->loadExtension($config, $configurator, $builder);

        return $builder;
    }
}
