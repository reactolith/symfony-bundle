# Reactolith Symfony Bundle

Symfony integration for [Reactolith](https://github.com/reactolith/reactolith) -- server-side HTML hydration with React/shadcn components.

**Write your UI in Twig. Get React-powered shadcn/ui components. No JavaScript in your Symfony code.**

Reactolith lets you render HTML with custom tags like `<ui-button>`, `<ui-input>`, `<ui-select>` from your Symfony backend. On the client side, Reactolith automatically hydrates these tags into fully interactive React components (based on [shadcn/ui](https://ui.shadcn.com/)). This bundle provides the Symfony-side integration:

- **Twig Form Theme** -- Symfony forms automatically render `<ui-*>` HTML tags
- **`re_attrs` Filter** -- Renders prop objects as correct HTML attributes (string, boolean, JSON)
- **HTTP/2 Preload** -- Optional event listener that sends `X-Reactolith-Components` header with all tags on the page
- **Custom FormTypes** -- `SwitchType` and more

## Installation

```bash
composer require reactolith/symfony-bundle pentatrion/vite-bundle
```

If you're using Symfony Flex, the bundles are registered automatically. Otherwise, add them to your `config/bundles.php`:

```php
return [
    // ...
    Pentatrion\ViteBundle\PentatrionViteBundle::class => ['all' => true],
    Reactolith\SymfonyBundle\ReactolithBundle::class => ['all' => true],
];
```

## Quick Start

### 1. Vite Setup

```bash
npm install vite vite-plugin-symfony @vitejs/plugin-react @tailwindcss/vite
```

```ts
// vite.config.ts
import symfonyPlugin from "vite-plugin-symfony";
import tailwindcss from "@tailwindcss/vite";
import react from "@vitejs/plugin-react";
import { defineConfig } from "vite";

export default defineConfig({
    plugins: [react(), tailwindcss(), symfonyPlugin()],
    build: {
        rollupOptions: {
            input: {
                app: "./assets/app.ts",
            },
        },
    },
});
```

### 2. Base Template

```twig
{# templates/base.html.twig #}
<!DOCTYPE html>
<html>
    <head>
        {% block stylesheets %}
            {{ vite_entry_link_tags('app') }}
        {% endblock %}
        {% block javascripts %}
            {{ vite_entry_script_tags('app', { dependency: 'react' }) }}
        {% endblock %}
    </head>
    <body>
        {% block body %}{% endblock %}
    </body>
</html>
```

Vite asset tags are handled entirely by `pentatrion/vite-bundle` -- the Reactolith bundle does not duplicate this.

### 3. Use Components in Twig

```twig
{# Directly in templates #}
<ui-button variant="outline" size="lg">Click me</ui-button>

{# With dynamic props via re_attrs #}
<ui-toaster position="top-right" rich-colors {{ {toasts: toasts}|re_attrs }} />

{# Forms render automatically as <ui-*> tags #}
{{ form(form) }}
```

### 4. Resulting HTML Output

The form theme transforms Symfony forms into Reactolith-compatible HTML:

```html
<form name="contact" method="post">
  <div class="space-y-2">
    <ui-label for="contact_name">Your Name</ui-label>
    <ui-input type="text" id="contact_name" name="contact[name]" required />
  </div>
  <div class="space-y-2">
    <ui-label for="contact_email">Email</ui-label>
    <ui-input type="email" id="contact_email" name="contact[email]" required />
  </div>
  <div class="space-y-2">
    <ui-button type="submit">Submit</ui-button>
  </div>
</form>
```

## Configuration

```yaml
# config/packages/reactolith.yaml
reactolith:
  tag_prefix: 'ui-'          # HTML tag prefix for components
  preload:
    enabled: false            # opt-in HTTP/2 preload headers
  form_theme:
    enabled: true             # auto-registers the form theme globally
```

| Option | Default | Description |
|--------|---------|-------------|
| `tag_prefix` | `ui-` | HTML tag prefix for all Reactolith components. |
| `preload.enabled` | `false` | Enable the HTTP/2 component preload listener. |
| `form_theme.enabled` | `true` | Auto-register the Reactolith form theme globally. |

## `re_attrs` Filter / Function

Renders an associative array as HTML attributes. Available as both a Twig **filter** and **function**.

```twig
{# As a filter #}
<ui-toaster {{ {position: 'top-right', 'rich-colors': true, toasts: toasts}|re_attrs }} />

{# As a function #}
<ui-dialog {{ re_attrs({open: isOpen, config: {animate: true}}) }}>
```

### Rendering Rules

| Value Type | Output | Example |
|-----------|--------|---------|
| String | `name="value"` | `variant="outline"` |
| Number | `name="42"` | `count="42"` |
| `true` | `name` | `disabled` |
| `false` / `null` | *(omitted)* | |
| Array / Object | `json-name='...'` | `json-config='{"a":1}'` |

This replaces verbose manual encoding like `json-toasts="{{ toasts|json_encode }}"` with `{{ {toasts: toasts}|re_attrs }}`.

## HTTP/2 Component Preloading

When enabled, the listener scans each HTML response for `<ui-*>` tags and adds a response header:

```yaml
reactolith:
  preload:
    enabled: true
```

```
X-Reactolith-Components: ui-button, ui-input, ui-label, ui-toaster
```

A reverse proxy or CDN can use this header to push the corresponding JavaScript chunks via HTTP/2.

## Form Theme

### Supported Mappings

| Symfony Type | Reactolith Tag |
|-------------|---------------|
| `TextType` | `<ui-input type="text">` |
| `EmailType` | `<ui-input type="email">` |
| `PasswordType` | `<ui-input type="password">` |
| `NumberType` | `<ui-input type="number">` |
| `UrlType` | `<ui-input type="url">` |
| `SearchType` | `<ui-input type="search">` |
| `TelType` | `<ui-input type="tel">` |
| `TextareaType` | `<ui-textarea>` |
| `CheckboxType` | `<ui-checkbox>` |
| `SwitchType` (custom) | `<ui-switch>` |
| `ChoiceType` (select) | `<ui-select>` |
| `ChoiceType` (radio) | `<ui-radio-group>` |
| `SubmitType` | `<ui-button type="submit">` |
| `ButtonType` | `<ui-button>` |

Unsupported types fall back to Symfony's `form_div_layout.html.twig`.

### Form Row Structure

```html
<div class="space-y-2">
  <ui-label for="...">Label</ui-label>
  <!-- widget -->
  <p class="text-sm text-muted-foreground">Help text</p>
  <p class="text-sm font-medium text-destructive">Error message</p>
</div>
```

### SwitchType

```php
use Reactolith\SymfonyBundle\Form\Type\SwitchType;

$builder->add('darkMode', SwitchType::class, ['label' => 'Enable dark mode']);
```

```html
<ui-switch id="form_darkMode" name="form[darkMode]" json-checked="false" />
```

### Custom Tag Prefix

```yaml
reactolith:
  tag_prefix: 'x-'
```

```html
<x-input type="text" ... />
<x-button type="submit">Submit</x-button>
```

### Overriding the Form Theme

Per form:

```twig
{% form_theme form '@Reactolith/form/reactolith_layout.html.twig' %}
```

Extend it:

```twig
{# templates/form/my_theme.html.twig #}
{% use "@Reactolith/form/reactolith_layout.html.twig" %}

{% block form_row %}
<div class="my-custom-wrapper">
  {{ form_label(form) }}
  {{ form_widget(form) }}
  {{ form_errors(form) }}
</div>
{% endblock %}
```

Disable global registration:

```yaml
reactolith:
  form_theme:
    enabled: false
```

## License

MIT License. See [LICENSE](LICENSE) for details.
