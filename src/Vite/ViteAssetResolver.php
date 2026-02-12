<?php

namespace Reactolith\SymfonyBundle\Vite;

class ViteAssetResolver
{
    private ?array $manifest = null;
    private string $publicDir;

    public function __construct(
        string $projectDir,
        private array $config,
    ) {
        $this->publicDir = $projectDir . '/public';
    }

    public function isDevMode(): bool
    {
        return !empty($this->config['dev_server_url']);
    }

    public function getScriptTags(): string
    {
        if ($this->isDevMode()) {
            return $this->getDevScriptTags();
        }

        return $this->getProductionScriptTags();
    }

    public function getStyleTags(): string
    {
        if ($this->isDevMode()) {
            return ''; // Vite dev server injects CSS via JS
        }

        return $this->getProductionStyleTags();
    }

    /**
     * Returns asset URLs suitable for Link preload headers.
     *
     * @return list<array{url: string, type: string}>
     */
    public function getPreloadLinks(): array
    {
        if ($this->isDevMode()) {
            return [];
        }

        $manifest = $this->getManifest();
        $basePath = '/' . trim($this->config['build_directory'], '/') . '/';
        $links = [];

        foreach ($this->config['entry_points'] as $entry) {
            if (!isset($manifest[$entry])) {
                continue;
            }

            $links[] = ['url' => $basePath . $manifest[$entry]['file'], 'type' => 'script'];

            foreach ($manifest[$entry]['css'] ?? [] as $cssFile) {
                $links[] = ['url' => $basePath . $cssFile, 'type' => 'style'];
            }
        }

        return $links;
    }

    private function getDevScriptTags(): string
    {
        $devUrl = rtrim($this->config['dev_server_url'], '/');
        $tags = sprintf('<script type="module" src="%s/@vite/client"></script>', htmlspecialchars($devUrl, ENT_QUOTES, 'UTF-8')) . "\n";

        foreach ($this->config['entry_points'] as $entry) {
            $tags .= sprintf(
                '<script type="module" src="%s/%s"></script>',
                htmlspecialchars($devUrl, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($entry, ENT_QUOTES, 'UTF-8'),
            ) . "\n";
        }

        return $tags;
    }

    private function getProductionScriptTags(): string
    {
        $manifest = $this->getManifest();
        $basePath = '/' . trim($this->config['build_directory'], '/') . '/';
        $tags = '';

        foreach ($this->config['entry_points'] as $entry) {
            if (!isset($manifest[$entry])) {
                continue;
            }

            $tags .= sprintf(
                '<script type="module" src="%s"></script>',
                htmlspecialchars($basePath . $manifest[$entry]['file'], ENT_QUOTES, 'UTF-8'),
            ) . "\n";
        }

        return $tags;
    }

    private function getProductionStyleTags(): string
    {
        $manifest = $this->getManifest();
        $basePath = '/' . trim($this->config['build_directory'], '/') . '/';
        $tags = '';

        foreach ($this->config['entry_points'] as $entry) {
            if (!isset($manifest[$entry]['css'])) {
                continue;
            }

            foreach ($manifest[$entry]['css'] as $cssFile) {
                $tags .= sprintf(
                    '<link rel="stylesheet" href="%s">',
                    htmlspecialchars($basePath . $cssFile, ENT_QUOTES, 'UTF-8'),
                ) . "\n";
            }
        }

        return $tags;
    }

    private function getManifest(): array
    {
        if ($this->manifest !== null) {
            return $this->manifest;
        }

        $buildDir = $this->publicDir . '/' . trim($this->config['build_directory'], '/');

        // Try Vite 5+ location first
        $manifestPath = $buildDir . '/.vite/manifest.json';
        if (!file_exists($manifestPath)) {
            // Fallback to Vite 4 location
            $manifestPath = $buildDir . '/manifest.json';
        }

        if (!file_exists($manifestPath)) {
            return $this->manifest = [];
        }

        $content = file_get_contents($manifestPath);
        $this->manifest = json_decode($content, true) ?? [];

        return $this->manifest;
    }
}
