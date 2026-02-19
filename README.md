# Reactolith Symfony Bundle

Symfony integration for [Reactolith](https://github.com/reactolith/reactolith) -- server-side HTML hydration with React components.

**Write your UI in Twig. Get React-powered interactive components. No JavaScript in your Symfony code.**

Reactolith lets you render HTML with custom tags like `<ui-button>`, `<ui-input>`, `<ui-select>` from your Symfony backend. On the client side, Reactolith automatically hydrates these tags into fully interactive React components. This bundle provides the Symfony-side integration:

- **Twig Form Theme** -- Symfony forms automatically render `<ui-*>` HTML tags
- **`re_attrs` Filter** -- Renders prop objects as correct HTML attributes (string, boolean, JSON)
- **HTTP/2 Preload** -- Optional event listener that sends `X-Reactolith-Components` header with all tags on the page
- **Custom FormTypes** -- `SwitchType` and more

## Requirements

- PHP >= 8.2
- Symfony 6.4, 7.x, or 8.x

## Installation

### Step 1: Configure Symfony Flex (recommended)

To enable automatic bundle registration, add the Reactolith recipe endpoint to your `composer.json`:

```json
{
    "extra": {
        "symfony": {
            "allow-contrib": true,
            "endpoint": [
                "https://api.github.com/repos/reactolith/symfony-bundle/contents/flex/main/index.json",
                "flex://defaults"
            ]
        }
    }
}
```

### Step 2: Install the packages

```bash
composer require reactolith/symfony-bundle pentatrion/vite-bundle
```

With the Flex endpoint configured, both bundles are registered in `config/bundles.php` automatically.

<details>
<summary><strong>Without Flex:</strong> manual bundle registration</summary>

If you don't want to configure the Flex endpoint, add both bundles to `config/bundles.php` manually:

```php
return [
    // ...
    Pentatrion\ViteBundle\PentatrionViteBundle::class => ['all' => true],
    Reactolith\SymfonyBundle\ReactolithBundle::class => ['all' => true],
];
```

</details>

## Quick Start

### 1. Install frontend dependencies

```bash
npm install reactolith react react-dom @loadable/component
npm install -D vite vite-plugin-symfony @vitejs/plugin-react @tailwindcss/vite tailwindcss
```

### 2. Vite configuration

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

### 3. CSS entry point

```css
/* assets/app.css */
@import "tailwindcss";
```

### 4. JavaScript entry point

Create `assets/app.ts` with the Reactolith setup:

```ts
// assets/app.ts
import "./app.css";
import loadable from "@loadable/component";
import { App } from "reactolith";

const component = loadable(
    async ({ is }: { is: string }) => {
        return import(`./components/ui/${is.substring(3)}.tsx`);
    },
    {
        cacheKey: ({ is }) => is,
        resolveComponent: (mod, { is }: { is: string }) => {
            const cmpName = is
                .substring(3)
                .replace(/(^\w|-\w)/g, (match) =>
                    match.replace(/-/, "").toUpperCase()
                );
            return mod[cmpName];
        },
    }
);

new App(component);
```

This maps `<ui-button>` to `./components/ui/button.tsx`, `<ui-input>` to `./components/ui/input.tsx`, etc. The `substring(3)` strips the `ui-` prefix.

### 5. Create your React components

Place your component files in `assets/components/ui/`. Each file should export a named component that matches its tag name in PascalCase:

```tsx
// assets/components/ui/button.tsx
import React from "react";

export function Button({ children, ...props }: React.ButtonHTMLAttributes<HTMLButtonElement>) {
    return <button className="px-4 py-2 rounded bg-primary text-primary-foreground" {...props}>{children}</button>;
}
```

### 6. Base template

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
        <div id="reactolith-app">
            {% block body %}{% endblock %}
        </div>
    </body>
</html>
```

> **Important**: The `<div id="reactolith-app">` wrapper is required -- Reactolith uses it as the root element for React hydration.

Vite asset tags are handled entirely by `pentatrion/vite-bundle` -- the Reactolith bundle does not duplicate this.

### 7. Use components in Twig

```twig
{# Directly in templates #}
<ui-button variant="outline" size="lg">Click me</ui-button>

{# With dynamic props via re_attrs #}
<ui-toaster position="top-right" rich-colors {{ {toasts: toasts}|re_attrs }} />

{# Forms render automatically as <ui-*> tags #}
{{ form(form) }}
```

### 8. Run the dev server

```bash
npx vite         # Start Vite dev server
php -S localhost:8000 -t public  # Start Symfony dev server (or use symfony serve)
```

### Resulting HTML Output

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

## Using shadcn/ui components

Instead of writing your own components, you can use [shadcn/ui](https://ui.shadcn.com/) or [Reactolith UI](https://reactolith.github.io/ui/) to get pre-built, styled components that work with Reactolith.

### Option A: Start with shadcn/create (recommended for new projects)

The fastest way to get a working setup with styled components:

```bash
npx shadcn@latest create \
  --preset "https://ui.shadcn.com/init?base=radix&style=nova&baseColor=neutral&theme=neutral&iconLibrary=lucide&font=inter&menuAccent=subtle&menuColor=default&radius=default&template=vite&rtl=false" \
  --template vite \
  my-frontend
```

This scaffolds a complete Vite + React + Tailwind + shadcn/ui project. Then copy the generated `src/components/ui/` directory into your Symfony project's `assets/components/ui/` folder and adapt the entry point.

### Option B: Add shadcn components to an existing project

```bash
npx shadcn@latest init
npx shadcn@latest add button input textarea select checkbox label
```

This creates components in `src/components/ui/` (or your configured path). Move them to `assets/components/ui/` and adjust imports as needed.

### Option C: Use the Reactolith UI registry

[Reactolith UI](https://reactolith.github.io/ui/) provides a shadcn-compatible component registry specifically tailored for Reactolith:

```bash
npx shadcn@latest add -r https://reactolith.github.io/ui button input textarea select checkbox label switch
```

These components are pre-configured to work seamlessly with Reactolith's hydration model.

### shadcn/ui entry point example

With shadcn/ui components in place, your `app.ts` stays the same -- the loadable resolver maps `<ui-button>` to `button.tsx`, `<ui-input>` to `input.tsx`, etc. Since shadcn components use named exports, the `resolveComponent` callback handles the PascalCase conversion:

```ts
// assets/app.ts
import "./app.css";
import loadable from "@loadable/component";
import { App } from "reactolith";

const component = loadable(
    async ({ is }: { is: string }) => {
        return import(`./components/ui/${is.substring(3)}.tsx`);
    },
    {
        cacheKey: ({ is }) => is,
        resolveComponent: (mod, { is }: { is: string }) => {
            // ui-button -> Button, ui-radio-group -> RadioGroup
            const cmpName = is
                .substring(3)
                .replace(/(^\w|-\w)/g, (match) =>
                    match.replace(/-/, "").toUpperCase()
                );
            return mod[cmpName];
        },
    }
);

new App(component);
```

## Configuration

```yaml
# config/packages/reactolith.yaml
reactolith:
  tag_prefix: 'ui-'          # must match /^[a-z][a-z0-9]*-$/
  preload:
    enabled: false            # opt-in HTTP/2 preload headers
  form_theme:
    enabled: true             # auto-registers the form theme globally
```

Shorthand:

```yaml
reactolith:
  preload: true               # same as preload: { enabled: true }
  form_theme: false            # same as form_theme: { enabled: false }
```

| Option | Default | Description |
|--------|---------|-------------|
| `tag_prefix` | `ui-` | HTML tag prefix. Must be lowercase, ending with `-`. |
| `preload` | `false` | Enable the HTTP/2 component preload listener. |
| `form_theme` | `true` | Auto-register the Reactolith form theme globally. |

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
