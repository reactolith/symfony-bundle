<?php

namespace Reactolith\SymfonyBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\Twig\ReactolithTwigExtension;

class ReactolithTwigExtensionTest extends TestCase
{
    private ReactolithTwigExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new ReactolithTwigExtension([
            'root_selector' => '#reactolith',
            'tag_prefix' => 'ui-',
            'mercure' => [
                'enabled' => false,
                'hub_url' => null,
                'with_credentials' => false,
            ],
        ]);
    }

    public function testRootOpenWithoutOptions(): void
    {
        $html = $this->extension->rootOpen();

        $this->assertStringContainsString('<div', $html);
        $this->assertStringContainsString('id="reactolith"', $html);
    }

    public function testRootOpenWithCustomOptions(): void
    {
        $html = $this->extension->rootOpen(['id' => 'custom', 'class' => 'my-class']);

        $this->assertStringContainsString('id="custom"', $html);
        $this->assertStringContainsString('class="my-class"', $html);
    }

    public function testRootOpenWithMercureConfig(): void
    {
        $extension = new ReactolithTwigExtension([
            'root_selector' => '#reactolith',
            'tag_prefix' => 'ui-',
            'mercure' => [
                'enabled' => true,
                'hub_url' => 'https://example.com/.well-known/mercure',
                'with_credentials' => true,
            ],
        ]);

        $html = $extension->rootOpen();

        $this->assertStringContainsString('data-mercure-hub-url="https://example.com/.well-known/mercure"', $html);
        $this->assertStringContainsString('data-mercure-with-credentials', $html);
    }

    public function testRootOpenWithMercureDisabled(): void
    {
        $html = $this->extension->rootOpen();

        $this->assertStringNotContainsString('data-mercure', $html);
    }

    public function testRootClose(): void
    {
        $html = $this->extension->rootClose();

        $this->assertSame('</div>', $html);
    }

    public function testAttrWithStringValue(): void
    {
        $result = $this->extension->attr('name', 'value');

        $this->assertSame('name="value"', $result);
    }

    public function testAttrWithBooleanTrue(): void
    {
        $result = $this->extension->attr('disabled', true);

        $this->assertSame('disabled', $result);
    }

    public function testAttrWithBooleanFalse(): void
    {
        $result = $this->extension->attr('disabled', false);

        $this->assertSame('', $result);
    }

    public function testAttrWithArrayValue(): void
    {
        $result = $this->extension->attr('config', ['foo' => 'bar']);

        $this->assertStringContainsString('json-config=', $result);
        $this->assertStringContainsString('{"foo":"bar"}', $result);
    }

    public function testAttrWithObjectValue(): void
    {
        $obj = new \stdClass();
        $obj->theme = 'dark';

        $result = $this->extension->attr('config', $obj);

        $this->assertStringContainsString('json-config=', $result);
        $this->assertStringContainsString('{"theme":"dark"}', $result);
    }

    public function testAttrWithNumericValue(): void
    {
        $result = $this->extension->attr('count', 42);

        $this->assertSame('count="42"', $result);
    }

    public function testRootOpenWithMercureAutoDetection(): void
    {
        $mockHub = new class {
            public function getPublicUrl(): string
            {
                return 'https://auto-detected.example.com/.well-known/mercure';
            }
        };

        $extension = new ReactolithTwigExtension([
            'root_selector' => '#reactolith',
            'tag_prefix' => 'ui-',
            'mercure' => [
                'enabled' => true,
                'hub_url' => null,
                'with_credentials' => false,
            ],
        ], $mockHub);

        $html = $extension->rootOpen();

        $this->assertStringContainsString('data-mercure-hub-url="https://auto-detected.example.com/.well-known/mercure"', $html);
    }

    public function testRootOpenWithCustomRootSelector(): void
    {
        $extension = new ReactolithTwigExtension([
            'root_selector' => '#my-app',
            'tag_prefix' => 'ui-',
            'mercure' => [
                'enabled' => false,
                'hub_url' => null,
                'with_credentials' => false,
            ],
        ]);

        $html = $extension->rootOpen();

        $this->assertStringContainsString('id="my-app"', $html);
    }

    public function testGetFunctions(): void
    {
        $functions = $this->extension->getFunctions();

        $this->assertCount(3, $functions);

        $names = array_map(fn ($f) => $f->getName(), $functions);
        $this->assertContains('reactolith_root_open', $names);
        $this->assertContains('reactolith_root_close', $names);
        $this->assertContains('reactolith_attr', $names);
    }

    public function testAttrSpecialCharactersAreEscaped(): void
    {
        $result = $this->extension->attr('title', 'He said "hello" & goodbye');

        $this->assertStringContainsString('title="He said &quot;hello&quot; &amp; goodbye"', $result);
    }

    public function testRootOpenWithMercureWithoutCredentials(): void
    {
        $extension = new ReactolithTwigExtension([
            'root_selector' => '#reactolith',
            'tag_prefix' => 'ui-',
            'mercure' => [
                'enabled' => true,
                'hub_url' => 'https://example.com/.well-known/mercure',
                'with_credentials' => false,
            ],
        ]);

        $html = $extension->rootOpen();

        $this->assertStringContainsString('data-mercure-hub-url=', $html);
        $this->assertStringNotContainsString('data-mercure-with-credentials', $html);
    }
}
