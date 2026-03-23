<?php

namespace Reactolith\SymfonyBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ReactolithTwigExtension extends AbstractExtension
{
    /**
     * Maps lowercase HTML attribute names to their React/camelCase equivalents
     * needed when rendering inside React-based web components.
     */
    private const CAMEL_CASE_ATTRS = [
        'autocomplete' => 'autoComplete',
        'autofocus'    => 'autoFocus',
        'tabindex'     => 'tabIndex',
        'maxlength'    => 'maxLength',
        'minlength'    => 'minLength',
        'readonly'     => 'readOnly',
        'novalidate'   => 'noValidate',
        'formaction'   => 'formAction',
        'formenctype'  => 'formEncType',
        'formmethod'   => 'formMethod',
        'formnovalidate' => 'formNoValidate',
        'formtarget'   => 'formTarget',
        'accesskey'    => 'accessKey',
        'contenteditable' => 'contentEditable',
        'crossorigin'  => 'crossOrigin',
        'enterkeyhint' => 'enterKeyHint',
    ];

    public function getFilters(): array
    {
        return [
            new TwigFilter('re_attrs', $this->renderAttributes(...), ['is_safe' => ['html']]),
            new TwigFilter('re_attr_key', $this->mapAttributeName(...)),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('re_attrs', $this->renderAttributes(...), ['is_safe' => ['html']]),
        ];
    }

    public function mapAttributeName(string $name): string
    {
        return self::CAMEL_CASE_ATTRS[strtolower($name)] ?? $name;
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

            $mappedName = self::CAMEL_CASE_ATTRS[strtolower($name)] ?? $name;

            if ($value === true) {
                $parts[] = htmlspecialchars($mappedName, ENT_QUOTES, 'UTF-8');
                continue;
            }

            if (is_array($value) || is_object($value)) {
                $jsonValue = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $parts[] = sprintf(
                    "json-%s='%s'",
                    htmlspecialchars($mappedName, ENT_QUOTES, 'UTF-8'),
                    str_replace("'", '&#039;', $jsonValue),
                );
                continue;
            }

            $parts[] = sprintf(
                '%s="%s"',
                htmlspecialchars($mappedName, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'),
            );
        }

        return implode(' ', $parts);
    }
}
