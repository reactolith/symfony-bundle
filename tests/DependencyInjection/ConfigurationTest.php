<?php

namespace Reactolith\SymfonyBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    private Processor $processor;
    private Configuration $configuration;

    protected function setUp(): void
    {
        $this->processor = new Processor();
        $this->configuration = new Configuration();
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
        $config = $this->process([
            'tag_prefix' => 'x-',
        ]);

        $this->assertSame('x-', $config['tag_prefix']);
    }

    public function testPreloadEnabled(): void
    {
        $config = $this->process([
            'preload' => ['enabled' => true],
        ]);

        $this->assertTrue($config['preload']['enabled']);
    }

    public function testFormThemeDisabled(): void
    {
        $config = $this->process([
            'form_theme' => ['enabled' => false],
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

    public function testInvalidPreloadValueThrows(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidTypeException::class);

        $this->process([
            'preload' => ['enabled' => 'not-a-bool'],
        ]);
    }

    public function testInvalidFormThemeValueThrows(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidTypeException::class);

        $this->process([
            'form_theme' => ['enabled' => 'not-a-bool'],
        ]);
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
}
