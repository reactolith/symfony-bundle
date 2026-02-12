<?php

namespace Reactolith\SymfonyBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ReactolithTwigExtension extends AbstractExtension
{
    private array $config;
    private ?object $mercureHub;

    public function __construct(array $config = [], ?object $mercureHub = null)
    {
        $this->config = $config;
        $this->mercureHub = $mercureHub;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('reactolith_root_open', [$this, 'rootOpen'], ['is_safe' => ['html']]),
            new TwigFunction('reactolith_root_close', [$this, 'rootClose'], ['is_safe' => ['html']]),
            new TwigFunction('reactolith_attr', [$this, 'attr'], ['is_safe' => ['html']]),
        ];
    }

    public function rootOpen(array $options = []): string
    {
        $rootSelector = $this->config['root_selector'] ?? '#reactolith';
        $defaultId = ltrim($rootSelector, '#');

        $id = $options['id'] ?? $defaultId;
        $attrs = sprintf('id="%s"', htmlspecialchars($id, ENT_QUOTES, 'UTF-8'));

        if (isset($options['class'])) {
            $attrs .= sprintf(' class="%s"', htmlspecialchars($options['class'], ENT_QUOTES, 'UTF-8'));
        }

        // Add extra attributes from options (excluding id and class)
        foreach ($options as $key => $value) {
            if (in_array($key, ['id', 'class'], true)) {
                continue;
            }
            $attrs .= sprintf(' %s="%s"', htmlspecialchars($key, ENT_QUOTES, 'UTF-8'), htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
        }

        // Mercure integration
        $mercureConfig = $this->config['mercure'] ?? [];
        $mercureEnabled = $mercureConfig['enabled'] ?? true;

        if ($mercureEnabled) {
            $hubUrl = $mercureConfig['hub_url'] ?? null;

            // Auto-detect from MercureBundle if no manual URL is set
            if ($hubUrl === null && $this->mercureHub !== null) {
                // The HubInterface has a getPublicUrl() method
                if (method_exists($this->mercureHub, 'getPublicUrl')) {
                    $hubUrl = $this->mercureHub->getPublicUrl();
                } elseif (method_exists($this->mercureHub, 'getUrl')) {
                    $hubUrl = $this->mercureHub->getUrl();
                }
            }

            if ($hubUrl !== null) {
                $attrs .= sprintf(' data-mercure-hub-url="%s"', htmlspecialchars($hubUrl, ENT_QUOTES, 'UTF-8'));
            }

            $withCredentials = $mercureConfig['with_credentials'] ?? false;
            if ($withCredentials) {
                $attrs .= ' data-mercure-with-credentials';
            }
        }

        return sprintf('<div %s>', $attrs);
    }

    public function rootClose(): string
    {
        return '</div>';
    }

    public function attr(string $name, mixed $value): string
    {
        // Boolean false: attribute is omitted
        if ($value === false) {
            return '';
        }

        // Boolean true: attribute without value
        if ($value === true) {
            return htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        }

        // Arrays/Objects: json- prefix with JSON-encoded value
        if (is_array($value) || is_object($value)) {
            $jsonValue = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            return sprintf(
                "json-%s='%s'",
                htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                str_replace("'", '&#039;', $jsonValue)
            );
        }

        // String values: normal HTML attribute
        return sprintf(
            '%s="%s"',
            htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8')
        );
    }
}
