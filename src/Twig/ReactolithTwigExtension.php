<?php

namespace Reactolith\SymfonyBundle\Twig;

use Reactolith\SymfonyBundle\Vite\ViteAssetResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ReactolithTwigExtension extends AbstractExtension
{
    public function __construct(
        private array $config = [],
        private ?ViteAssetResolver $viteResolver = null,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('reactolith_attrs', $this->renderAttributes(...), ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('reactolith_attrs', $this->renderAttributes(...), ['is_safe' => ['html']]),
            new TwigFunction('reactolith_scripts', $this->renderScripts(...), ['is_safe' => ['html']]),
            new TwigFunction('reactolith_styles', $this->renderStyles(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * Renders an associative array as HTML attributes following Reactolith conventions.
     *
     * - String/Number: name="value"
     * - Boolean true:  name (attribute without value)
     * - Boolean false / null: omitted
     * - Array/Object:  json-name='{"encoded":"json"}'
     */
    public function renderAttributes(array $attributes): string
    {
        $parts = [];

        foreach ($attributes as $name => $value) {
            if ($value === false || $value === null) {
                continue;
            }

            if ($value === true) {
                $parts[] = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                continue;
            }

            if (is_array($value) || is_object($value)) {
                $jsonValue = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $parts[] = sprintf(
                    "json-%s='%s'",
                    htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                    str_replace("'", '&#039;', $jsonValue),
                );
                continue;
            }

            $parts[] = sprintf(
                '%s="%s"',
                htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'),
            );
        }

        return implode(' ', $parts);
    }

    public function renderScripts(): string
    {
        if ($this->viteResolver === null) {
            return '';
        }

        return $this->viteResolver->getScriptTags();
    }

    public function renderStyles(): string
    {
        if ($this->viteResolver === null) {
            return '';
        }

        return $this->viteResolver->getStyleTags();
    }
}
