# Reactolith Symfony Bundle

Symfony integration for [Reactolith](https://github.com/reactolith/reactolith) -- server-side HTML hydration with React components.

**Write your UI in Twig. Get React-powered interactive components. No JavaScript in your Symfony code.**

Reactolith lets you render HTML with custom tags like `<ui-button>`, `<ui-input>`, `<ui-select>` from your Symfony backend. On the client side, Reactolith automatically hydrates these tags into fully interactive React components.

This bundle provides the Symfony-side integration:

- **`re_attrs` Filter** -- Renders prop objects as correct HTML attributes (string, boolean, JSON)
- **HTTP/2 Preload** -- Optional event listener that sends `X-Reactolith-Components` header with all tags on the page
- **Custom FormTypes** -- `SwitchType` and more
- **Form Theme** -- Optional Twig form theme for [reactolith/ui](https://github.com/reactolith/ui) (shadcn-based components)

## Requirements

- PHP >= 8.2
- Symfony 6.4, 7.x, or 8.x

## Installation

```bash
composer require reactolith/symfony-bundle
```

The bundle registers itself automatically via Symfony Flex. If you're not using Flex, add it manually to `config/bundles.php`:

```php
Reactolith\SymfonyBundle\ReactolithBundle::class => ['all' => true],
```

## Configuration

```yaml
# config/packages/reactolith.yaml
reactolith:
    tag_prefix: 'ui-'   # HTML tag prefix, must match /^[a-z][a-z0-9]*-$/
    preload:
        enabled: false  # opt-in: sends X-Reactolith-Components response header
```

| Option | Default | Description |
|--------|---------|-------------|
| `tag_prefix` | `ui-` | HTML tag prefix for components. Must be lowercase, ending with `-`. |
| `preload` | `false` | Enable the HTTP/2 component preload listener. |

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

## Custom Tag Prefix

```yaml
reactolith:
    tag_prefix: 'x-'
```

```html
<x-input type="text" ... />
<x-button type="submit">Submit</x-button>
```

The prefix is available in Twig as the global `{{ reactolith_tag_prefix }}`.

---

## Using with reactolith/ui (shadcn)

[reactolith/ui](https://github.com/reactolith/ui) is a shadcn-based component library that works seamlessly with this bundle. The following steps set up a complete Symfony + Vite + React + shadcn/ui stack.

### 1. Install frontend dependencies

```bash
npm install reactolith react react-dom @loadable/component
npm install -D vite vite-plugin-symfony @vitejs/plugin-react @tailwindcss/vite tailwindcss
```

For shadcn/ui components:

```bash
npm install @base-ui/react class-variance-authority clsx tailwind-merge lucide-react
npm install -D @types/react @types/loadable__component
```

Optional (for enhanced styling):

```bash
npm install @fontsource-variable/inter tw-animate-css
```

### 2. Install pentatrion/vite-bundle

```bash
composer require pentatrion/vite-bundle
```

### 3. Initialize shadcn/ui with reactolith/ui registry

```bash
npx shadcn@latest init
```

When prompted, configure:
- TypeScript: Yes
- Style: base-nova (or your preference)
- Base color: neutral (or your preference)
- CSS variables: Yes
- Import alias: @/*
- React Server Components: No

After initialization, update `components.json` to include the reactolith/ui registry:

```json
{
  "$schema": "https://ui.shadcn.com/schema.json",
  "style": "base-nova",
  "rsc": false,
  "tsx": true,
  "tailwind": {
    "config": "",
    "css": "assets/app.css",
    "baseColor": "neutral",
    "cssVariables": true,
    "prefix": ""
  },
  "iconLibrary": "lucide",
  "rtl": false,
  "aliases": {
    "components": "@/components",
    "utils": "@/lib/utils",
    "ui": "@/components/ui",
    "lib": "@/lib",
    "hooks": "@/hooks"
  },
  "registries": {
    "@reactolith": "https://reactolith.github.io/ui/r/{name}.json"
  }
}
```

### 4. Add reactolith/ui components

```bash
npx shadcn@latest add @reactolith/button @reactolith/input @reactolith/label @reactolith/textarea
```

Components will be created in `assets/components/app/`.

### 5. Vite configuration

```js
// vite.config.js
import path from "path"
import symfonyPlugin from "vite-plugin-symfony";
import tailwindcss from "@tailwindcss/vite"
import react from "@vitejs/plugin-react"
import { defineConfig } from "vite"

export default defineConfig({
    plugins: [
        react(),
        tailwindcss(),
        symfonyPlugin({ refresh: true }),
    ],
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "./assets"),
        },
    },
    build: {
        rollupOptions: {
            input: {
                app: "./assets/app.ts",
            },
        },
    },
});
```

### 6. TypeScript configuration

Create `tsconfig.json`:

```json
{
  "files": [],
  "references": [
    { "path": "./tsconfig.app.json" },
    { "path": "./tsconfig.node.json" }
  ],
  "compilerOptions": {
    "baseUrl": ".",
    "paths": { "@/*": ["./assets/*"] }
  }
}
```

Create `tsconfig.app.json`:

```json
{
  "compilerOptions": {
    "target": "ES2022",
    "lib": ["ES2022", "DOM", "DOM.Iterable"],
    "module": "ESNext",
    "types": ["vite/client"],
    "skipLibCheck": true,
    "moduleResolution": "bundler",
    "allowImportingTsExtensions": true,
    "verbatimModuleSyntax": true,
    "noEmit": true,
    "jsx": "react-jsx",
    "strict": true,
    "baseUrl": ".",
    "paths": { "@/*": ["./assets/*"] }
  },
  "include": ["assets"]
}
```

Create `tsconfig.node.json`:

```json
{
  "compilerOptions": {
    "target": "ES2022",
    "lib": ["ES2022"],
    "module": "ESNext",
    "skipLibCheck": true,
    "moduleResolution": "bundler",
    "allowImportingTsExtensions": true,
    "verbatimModuleSyntax": true,
    "noEmit": true,
    "strict": true
  },
  "include": ["vite.config.js"]
}
```

### 7. CSS entry point

```css
/* assets/app.css */
@import "tailwindcss";
@import "tw-animate-css";
@import "shadcn/tailwind.css";
@import "@fontsource-variable/inter";

@custom-variant dark (&:is(.dark *));

/* Theme variables are auto-generated by shadcn init */
```

### 8. JavaScript entry point

Create `assets/app.ts`:

```ts
import "./app.css";
import loadable from "@loadable/component";
import { App } from "reactolith";
import type { ComponentType } from "react";

const modules = import.meta.glob<{ default: ComponentType<any> }>("@/components/app/**/*.tsx");

new App(
    loadable(({ is }: { is: string }) => {
        const name = is.substring(3)
        const match = Object.keys(modules).find(key => key.endsWith(`/${name}.tsx`));
        if (!match) throw new Error(`Component not found: ${is}`);
        return modules[match]();
    }, {
        cacheKey: ({ is }: { is: string }) => is,
    }) as unknown as ComponentType<Record<string, unknown>>,
);
```

This uses Vite's `import.meta.glob` to dynamically load components from `assets/components/app/`. Each component maps to `<ui-{name}>` by stripping the `ui-` prefix.

### 9. Base template

```twig
{# templates/base.html.twig #}
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>{% block title %}Welcome!{% endblock %}</title>
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

The `<div id="reactolith-app">` wrapper is required -- Reactolith uses it as the root element for React hydration.

### 10. Run the dev server

```bash
npx vite         # Start Vite dev server
symfony serve    # Start Symfony dev server
```

---

## Form Theme (reactolith/ui)

This bundle ships a Twig form theme that maps standard Symfony form types to `<ui-*>` tags. It is designed for use with reactolith/ui components.

To activate it, add it to your Twig configuration:

```yaml
# config/packages/twig.yaml
twig:
    form_themes:
        - '@Reactolith/form/reactolith_layout.html.twig'
```

Or apply it per form in a template:

```twig
{% form_theme form '@Reactolith/form/reactolith_layout.html.twig' %}
```

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

### Extending the Form Theme

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
