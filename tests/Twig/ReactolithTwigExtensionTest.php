<?php

namespace Reactolith\SymfonyBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\Twig\ReactolithTwigExtension;
use Reactolith\SymfonyBundle\Vite\ViteAssetResolver;

class ReactolithTwigExtensionTest extends TestCase
{
    private ReactolithTwigExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new ReactolithTwigExtension([
            'tag_prefix' => 'ui-',
        ]);
    }

    // --- renderAttributes (filter + function) ---

    public function testAttrsWithStringValue(): void
    {
        $result = $this->extension->renderAttributes(['name' => 'value']);

        $this->assertSame('name="value"', $result);
    }

    public function testAttrsWithMultipleStringValues(): void
    {
        $result = $this->extension->renderAttributes([
            'variant' => 'outline',
            'size' => 'lg',
        ]);

        $this->assertSame('variant="outline" size="lg"', $result);
    }

    public function testAttrsWithBooleanTrue(): void
    {
        $result = $this->extension->renderAttributes(['disabled' => true]);

        $this->assertSame('disabled', $result);
    }

    public function testAttrsWithBooleanFalse(): void
    {
        $result = $this->extension->renderAttributes(['disabled' => false]);

        $this->assertSame('', $result);
    }

    public function testAttrsWithNullIsOmitted(): void
    {
        $result = $this->extension->renderAttributes(['hidden' => null, 'name' => 'test']);

        $this->assertSame('name="test"', $result);
    }

    public function testAttrsWithArrayValue(): void
    {
        $result = $this->extension->renderAttributes(['config' => ['foo' => 'bar']]);

        $this->assertSame("json-config='{\"foo\":\"bar\"}'", $result);
    }

    public function testAttrsWithObjectValue(): void
    {
        $obj = new \stdClass();
        $obj->theme = 'dark';

        $result = $this->extension->renderAttributes(['config' => $obj]);

        $this->assertSame("json-config='{\"theme\":\"dark\"}'", $result);
    }

    public function testAttrsWithNumericValue(): void
    {
        $result = $this->extension->renderAttributes(['count' => 42]);

        $this->assertSame('count="42"', $result);
    }

    public function testAttrsSpecialCharactersAreEscaped(): void
    {
        $result = $this->extension->renderAttributes(['title' => 'He said "hello" & goodbye']);

        $this->assertSame('title="He said &quot;hello&quot; &amp; goodbye"', $result);
    }

    public function testAttrsMixedTypes(): void
    {
        $result = $this->extension->renderAttributes([
            'variant' => 'outline',
            'disabled' => true,
            'hidden' => false,
            'config' => ['theme' => 'dark'],
        ]);

        $this->assertSame("variant=\"outline\" disabled json-config='{\"theme\":\"dark\"}'", $result);
    }

    public function testAttrsEmptyArray(): void
    {
        $result = $this->extension->renderAttributes([]);

        $this->assertSame('', $result);
    }

    public function testAttrsJsonWithSingleQuotesEscaped(): void
    {
        $result = $this->extension->renderAttributes(['data' => ["it's" => 'fine']]);

        $this->assertStringContainsString('json-data=', $result);
        $this->assertStringContainsString("it&#039;s", $result);
    }

    // --- getFilters / getFunctions ---

    public function testProvidesFilterAndFunctions(): void
    {
        $filters = $this->extension->getFilters();
        $functions = $this->extension->getFunctions();

        $this->assertCount(1, $filters);
        $this->assertSame('re_attrs', $filters[0]->getName());

        $this->assertCount(3, $functions);
        $names = array_map(fn ($f) => $f->getName(), $functions);
        $this->assertContains('re_attrs', $names);
        $this->assertContains('re_scripts', $names);
        $this->assertContains('re_styles', $names);
    }

    // --- Vite helpers ---

    public function testRenderScriptsWithoutViteReturnsEmpty(): void
    {
        $result = $this->extension->renderScripts();

        $this->assertSame('', $result);
    }

    public function testRenderStylesWithoutViteReturnsEmpty(): void
    {
        $result = $this->extension->renderStyles();

        $this->assertSame('', $result);
    }

    public function testRenderScriptsWithViteDevServer(): void
    {
        $resolver = new ViteAssetResolver('/tmp/fake-project', [
            'enabled' => true,
            'build_directory' => 'build',
            'entry_points' => ['resources/js/app.js'],
            'dev_server_url' => 'http://localhost:5173',
        ]);

        $extension = new ReactolithTwigExtension([], $resolver);
        $result = $extension->renderScripts();

        $this->assertStringContainsString('<script type="module" src="http://localhost:5173/@vite/client"></script>', $result);
        $this->assertStringContainsString('<script type="module" src="http://localhost:5173/resources/js/app.js"></script>', $result);
    }

    public function testRenderStylesWithViteDevServerReturnsEmpty(): void
    {
        $resolver = new ViteAssetResolver('/tmp/fake-project', [
            'enabled' => true,
            'build_directory' => 'build',
            'entry_points' => ['resources/js/app.js'],
            'dev_server_url' => 'http://localhost:5173',
        ]);

        $extension = new ReactolithTwigExtension([], $resolver);
        $result = $extension->renderStyles();

        $this->assertSame('', $result);
    }
}
