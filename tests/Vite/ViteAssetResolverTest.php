<?php

namespace Reactolith\SymfonyBundle\Tests\Vite;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\Vite\ViteAssetResolver;

class ViteAssetResolverTest extends TestCase
{
    private string $fixtureDir;

    protected function setUp(): void
    {
        $this->fixtureDir = sys_get_temp_dir() . '/reactolith-test-' . uniqid();
        mkdir($this->fixtureDir . '/public/build/.vite', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->fixtureDir);
    }

    public function testIsDevModeWhenDevServerUrlSet(): void
    {
        $resolver = new ViteAssetResolver($this->fixtureDir, [
            'build_directory' => 'build',
            'entry_points' => ['resources/js/app.js'],
            'dev_server_url' => 'http://localhost:5173',
        ]);

        $this->assertTrue($resolver->isDevMode());
    }

    public function testIsNotDevModeWhenDevServerUrlIsNull(): void
    {
        $resolver = new ViteAssetResolver($this->fixtureDir, [
            'build_directory' => 'build',
            'entry_points' => ['resources/js/app.js'],
            'dev_server_url' => null,
        ]);

        $this->assertFalse($resolver->isDevMode());
    }

    public function testGetDevScriptTags(): void
    {
        $resolver = new ViteAssetResolver($this->fixtureDir, [
            'build_directory' => 'build',
            'entry_points' => ['resources/js/app.js'],
            'dev_server_url' => 'http://localhost:5173',
        ]);

        $html = $resolver->getScriptTags();

        $this->assertStringContainsString('<script type="module" src="http://localhost:5173/@vite/client"></script>', $html);
        $this->assertStringContainsString('<script type="module" src="http://localhost:5173/resources/js/app.js"></script>', $html);
    }

    public function testGetDevStyleTagsReturnsEmpty(): void
    {
        $resolver = new ViteAssetResolver($this->fixtureDir, [
            'build_directory' => 'build',
            'entry_points' => ['resources/js/app.js'],
            'dev_server_url' => 'http://localhost:5173',
        ]);

        $this->assertSame('', $resolver->getStyleTags());
    }

    public function testGetProductionScriptTagsFromVite5Manifest(): void
    {
        $this->writeManifest('.vite/manifest.json', [
            'resources/js/app.js' => [
                'file' => 'assets/app-abc123.js',
                'isEntry' => true,
            ],
        ]);

        $resolver = new ViteAssetResolver($this->fixtureDir, [
            'build_directory' => 'build',
            'entry_points' => ['resources/js/app.js'],
            'dev_server_url' => null,
        ]);

        $html = $resolver->getScriptTags();

        $this->assertStringContainsString('<script type="module" src="/build/assets/app-abc123.js"></script>', $html);
    }

    public function testGetProductionScriptTagsFromVite4Manifest(): void
    {
        // Remove .vite directory and write manifest at root level
        rmdir($this->fixtureDir . '/public/build/.vite');

        $this->writeManifest('manifest.json', [
            'resources/js/app.js' => [
                'file' => 'assets/app-legacy.js',
                'isEntry' => true,
            ],
        ]);

        $resolver = new ViteAssetResolver($this->fixtureDir, [
            'build_directory' => 'build',
            'entry_points' => ['resources/js/app.js'],
            'dev_server_url' => null,
        ]);

        $html = $resolver->getScriptTags();

        $this->assertStringContainsString('<script type="module" src="/build/assets/app-legacy.js"></script>', $html);
    }

    public function testGetProductionStyleTags(): void
    {
        $this->writeManifest('.vite/manifest.json', [
            'resources/js/app.js' => [
                'file' => 'assets/app-abc123.js',
                'isEntry' => true,
                'css' => ['assets/app-def456.css'],
            ],
        ]);

        $resolver = new ViteAssetResolver($this->fixtureDir, [
            'build_directory' => 'build',
            'entry_points' => ['resources/js/app.js'],
            'dev_server_url' => null,
        ]);

        $html = $resolver->getStyleTags();

        $this->assertStringContainsString('<link rel="stylesheet" href="/build/assets/app-def456.css">', $html);
    }

    public function testGetPreloadLinks(): void
    {
        $this->writeManifest('.vite/manifest.json', [
            'resources/js/app.js' => [
                'file' => 'assets/app-abc123.js',
                'isEntry' => true,
                'css' => ['assets/app-def456.css'],
            ],
        ]);

        $resolver = new ViteAssetResolver($this->fixtureDir, [
            'build_directory' => 'build',
            'entry_points' => ['resources/js/app.js'],
            'dev_server_url' => null,
        ]);

        $links = $resolver->getPreloadLinks();

        $this->assertCount(2, $links);
        $this->assertSame('/build/assets/app-abc123.js', $links[0]['url']);
        $this->assertSame('script', $links[0]['type']);
        $this->assertSame('/build/assets/app-def456.css', $links[1]['url']);
        $this->assertSame('style', $links[1]['type']);
    }

    public function testGetPreloadLinksInDevModeReturnsEmpty(): void
    {
        $resolver = new ViteAssetResolver($this->fixtureDir, [
            'build_directory' => 'build',
            'entry_points' => ['resources/js/app.js'],
            'dev_server_url' => 'http://localhost:5173',
        ]);

        $this->assertSame([], $resolver->getPreloadLinks());
    }

    public function testMissingManifestReturnsEmptyTags(): void
    {
        $resolver = new ViteAssetResolver($this->fixtureDir, [
            'build_directory' => 'nonexistent',
            'entry_points' => ['resources/js/app.js'],
            'dev_server_url' => null,
        ]);

        $this->assertSame('', $resolver->getScriptTags());
        $this->assertSame('', $resolver->getStyleTags());
        $this->assertSame([], $resolver->getPreloadLinks());
    }

    public function testMultipleEntryPoints(): void
    {
        $this->writeManifest('.vite/manifest.json', [
            'resources/js/app.js' => [
                'file' => 'assets/app-111.js',
                'isEntry' => true,
            ],
            'resources/js/admin.js' => [
                'file' => 'assets/admin-222.js',
                'isEntry' => true,
                'css' => ['assets/admin-333.css'],
            ],
        ]);

        $resolver = new ViteAssetResolver($this->fixtureDir, [
            'build_directory' => 'build',
            'entry_points' => ['resources/js/app.js', 'resources/js/admin.js'],
            'dev_server_url' => null,
        ]);

        $html = $resolver->getScriptTags();

        $this->assertStringContainsString('app-111.js', $html);
        $this->assertStringContainsString('admin-222.js', $html);

        $css = $resolver->getStyleTags();

        $this->assertStringContainsString('admin-333.css', $css);
    }

    private function writeManifest(string $relativePath, array $data): void
    {
        $path = $this->fixtureDir . '/public/build/' . $relativePath;
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($path, json_encode($data));
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($dir);
    }
}
