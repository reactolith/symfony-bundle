<?php

namespace Reactolith\SymfonyBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\ReactolithBundle;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    private Processor $processor;
    private ConfigurationInterface $configuration;

    protected function setUp(): void
    {
        $this->processor = new Processor();
        $this->configuration = $this->buildConfiguration();
    }

    public function testDefaultValues(): void
    {
        $config = $this->process([]);

        $this->assertSame('ui-', $config['tag_prefix']);
        $this->assertFalse($config['preload']['enabled']);
        $this->assertTrue($config['form_theme']['enabled']);
    }

    public function testCustomTagPrefix(): void
    {
        $config = $this->process(['tag_prefix' => 'x-']);

        $this->assertSame('x-', $config['tag_prefix']);
    }

    public function testTagPrefixWithNumbers(): void
    {
        $config = $this->process(['tag_prefix' => 'app2-']);

        $this->assertSame('app2-', $config['tag_prefix']);
    }

    public function testTagPrefixMustEndWithDash(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/tag prefix/i');

        $this->process(['tag_prefix' => 'ui']);
    }

    public function testTagPrefixCannotBeEmpty(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->process(['tag_prefix' => '']);
    }

    public function testTagPrefixRejectsInvalidCharacters(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->process(['tag_prefix' => 'ui_comp-']);
    }

    public function testPreloadCanBeEnabled(): void
    {
        $config = $this->process([
            'preload' => ['enabled' => true],
        ]);

        $this->assertTrue($config['preload']['enabled']);
    }

    public function testPreloadShorthandEnable(): void
    {
        // canBeEnabled() allows `preload: true` as shorthand
        $config = $this->process([
            'preload' => true,
        ]);

        $this->assertTrue($config['preload']['enabled']);
    }

    public function testFormThemeCanBeDisabled(): void
    {
        $config = $this->process([
            'form_theme' => ['enabled' => false],
        ]);

        $this->assertFalse($config['form_theme']['enabled']);
    }

    public function testFormThemeShorthandDisable(): void
    {
        // canBeDisabled() allows `form_theme: false` as shorthand
        $config = $this->process([
            'form_theme' => false,
        ]);

        $this->assertFalse($config['form_theme']['enabled']);
    }

    public function testFullCustomConfig(): void
    {
        $config = $this->process([
            'tag_prefix' => 'app-',
            'preload' => ['enabled' => true],
            'form_theme' => ['enabled' => false],
        ]);

        $this->assertSame('app-', $config['tag_prefix']);
        $this->assertTrue($config['preload']['enabled']);
        $this->assertFalse($config['form_theme']['enabled']);
    }

    public function testTreeBuilderRootName(): void
    {
        $tree = $this->configuration->getConfigTreeBuilder();

        $this->assertSame('reactolith', $tree->buildTree()->getName());
    }

    public function testMultipleConfigsMerged(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [
            ['tag_prefix' => 'a-'],
            ['tag_prefix' => 'b-'],
        ]);

        $this->assertSame('b-', $config['tag_prefix']);
    }

    private function process(array $input): array
    {
        return $this->processor->processConfiguration($this->configuration, [$input]);
    }

    private function buildConfiguration(): ConfigurationInterface
    {
        $bundle = new ReactolithBundle();
        $treeBuilder = new \Symfony\Component\Config\Definition\Builder\TreeBuilder('reactolith');
        $locator = new \Symfony\Component\Config\FileLocator(__DIR__);
        $loader = new \Symfony\Component\Config\Definition\Loader\DefinitionFileLoader($treeBuilder, $locator);
        $configurator = new DefinitionConfigurator($treeBuilder, $loader, __DIR__, 'test.php');
        $bundle->configure($configurator);

        return new class($treeBuilder) implements ConfigurationInterface {
            public function __construct(private \Symfony\Component\Config\Definition\Builder\TreeBuilder $treeBuilder) {}
            public function getConfigTreeBuilder(): \Symfony\Component\Config\Definition\Builder\TreeBuilder
            {
                return $this->treeBuilder;
            }
        };
    }
}
