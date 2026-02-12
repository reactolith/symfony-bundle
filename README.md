# Reactolith Symfony Bundle

Symfony integration for [Reactolith](https://github.com/reactolith/reactolith) -- server-side HTML hydration with React/shadcn components.

**Write your UI in Twig. Get React-powered shadcn/ui components. No JavaScript in your Symfony code.**

Reactolith lets you render HTML with custom tags like `<ui-button>`, `<ui-input>`, `<ui-select>` from your Symfony backend. On the client side, Reactolith automatically hydrates these tags into fully interactive React components (based on [shadcn/ui](https://ui.shadcn.com/)). This bundle provides the Symfony-side integration:

- **Twig Form Theme** -- Symfony forms automatically render `<ui-*>` HTML tags
- **Attribute Filter** -- `reactolith_attrs` renders prop objects as correct HTML attributes (string, boolean, JSON)
- **Vite Integration** -- Out-of-the-box `{{ reactolith_scripts() }}` / `{{ reactolith_styles() }}` with dev server and production manifest support
- **HTTP/2 Preload** -- Optional event listener that detects component tags in the response and sends preload headers
- **Custom FormTypes** -- Additional form types like `SwitchType`

## Installation

```bash
composer require reactolith/symfony-bundle
```

If you're using Symfony Flex, the bundle is registered automatically. Otherwise, add it to your `config/bundles.php`:

```php
return [
    // ...
    Reactolith\SymfonyBundle\ReactolithBundle::class => ['all' => true],
];
```

## Quick Start

### 1. Create a Symfony Form

```php
// src/Form/ContactType.php
namespace App\Form;

use Reactolith\SymfonyBundle\Form\Type\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Your Name'])
            ->add('email', EmailType::class)
            ->add('message', TextareaType::class)
            ->add('newsletter', SwitchType::class, ['label' => 'Subscribe to newsletter'])
            ->add('submit', SubmitType::class);
    }
}
```

### 2. Render in Twig

```twig
{# templates/contact.html.twig #}
{% extends 'base.html.twig' %}

{% block body %}
  <h1>Contact Us</h1>
  {{ form(form) }}
{% endblock %}

{% block javascripts %}
  {{ reactolith_scripts() }}
{% endblock %}

{% block stylesheets %}
  {{ reactolith_styles() }}
{% endblock %}
```

### 3. Resulting HTML Output

The form theme automatically transforms Symfony's standard form rendering into Reactolith-compatible HTML:

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
    <ui-label for="contact_message">Message</ui-label>
    <ui-textarea id="contact_message" name="contact[message]" required></ui-textarea>
  </div>
  <div class="space-y-2">
    <ui-label for="contact_newsletter">Subscribe to newsletter</ui-label>
    <ui-switch id="contact_newsletter" name="contact[newsletter]" json-checked="false" />
  </div>
  <div class="space-y-2">
    <ui-button type="submit" id="contact_submit" name="contact[submit]">Submit</ui-button>
  </div>
</form>
```

Reactolith on the client side hydrates each `<ui-*>` tag into its corresponding React/shadcn component.

## Configuration

Create `config/packages/reactolith.yaml`:

```yaml
reactolith:
  # HTML tag prefix for components (e.g. "ui-" -> <ui-button>, <ui-input>)
  tag_prefix: 'ui-'

  # Vite asset integration
  vite:
    enabled: true
    build_directory: 'build'                 # relative to public/
    entry_points:
      - 'resources/js/app.js'
    dev_server_url: ~                        # e.g. 'http://localhost:5173' for dev

  # HTTP/2 preload headers
  preload:
    enabled: false                           # opt-in

  # Form theme
  form_theme:
    enabled: true                            # auto-registers the form theme globally
```

### Configuration Options

| Option | Default | Description |
|--------|---------|-------------|
| `tag_prefix` | `ui-` | HTML tag prefix for all Reactolith components. Change this if `ui-` conflicts with your project. |
| `vite.enabled` | `true` | Enable Vite asset integration. |
| `vite.build_directory` | `build` | Vite build output directory, relative to `public/`. |
| `vite.entry_points` | `['resources/js/app.js']` | List of Vite entry point files. |
| `vite.dev_server_url` | `null` | Vite dev server URL. Set to e.g. `http://localhost:5173` during development. Leave `null` for production (reads manifest). |
| `preload.enabled` | `false` | Enable the HTTP/2 component preload listener. |
| `form_theme.enabled` | `true` | Whether to auto-register the Reactolith form theme globally. |

## Twig Filter / Function: `reactolith_attrs`

Renders an associative array as HTML attributes following Reactolith conventions. Available as both a **filter** and a **function**.

```twig
{# As a filter #}
<ui-button {{ {variant: 'outline', disabled: true, config: {theme: 'dark'}}|reactolith_attrs }}>
  Click me
</ui-button>

{# As a function #}
<ui-button {{ reactolith_attrs({variant: 'outline', size: 'lg'}) }}>
  Click me
</ui-button>
```

Output:

```html
<ui-button variant="outline" disabled json-config='{"theme":"dark"}'>
  Click me
</ui-button>
```

### Attribute rendering rules (Reactolith conventions)

| Value Type | Output | Example Input | Example Output |
|-----------|--------|---------------|----------------|
| String | `name="value"` | `{variant: 'outline'}` | `variant="outline"` |
| Number | `name="value"` | `{count: 42}` | `count="42"` |
| Boolean `true` | `name` (no value) | `{disabled: true}` | `disabled` |
| Boolean `false` | *(omitted)* | `{disabled: false}` | *(nothing)* |
| `null` | *(omitted)* | `{hidden: null}` | *(nothing)* |
| Array/Object | `json-name='...'` | `{config: {a: 1}}` | `json-config='{"a":1}'` |

## Vite Integration

The bundle provides out-of-the-box Vite support with `{{ reactolith_scripts() }}` and `{{ reactolith_styles() }}`.

### Development

Set the dev server URL in your config (or via environment-specific config):

```yaml
# config/packages/dev/reactolith.yaml
reactolith:
  vite:
    dev_server_url: 'http://localhost:5173'
```

The bundle will output:

```html
<script type="module" src="http://localhost:5173/@vite/client"></script>
<script type="module" src="http://localhost:5173/resources/js/app.js"></script>
```

### Production

With `dev_server_url: ~` (the default), the bundle reads the Vite manifest from `public/{build_directory}/.vite/manifest.json` (Vite 5+) or `public/{build_directory}/manifest.json` (Vite 4) and outputs the correct hashed asset URLs:

```html
<script type="module" src="/build/assets/app-BhF9KQ3W.js"></script>
<link rel="stylesheet" href="/build/assets/app-CJGkQYKR.css">
```

### Multiple Entry Points

```yaml
reactolith:
  vite:
    entry_points:
      - 'resources/js/app.js'
      - 'resources/js/admin.js'
```

## HTTP/2 Component Preloading

Enable the preload listener to automatically detect which Reactolith components are used on each page and send that information as response headers:

```yaml
reactolith:
  preload:
    enabled: true
```

The listener scans the HTML response for `<ui-*>` tags and adds:

**Component discovery header:**
```
X-Reactolith-Components: ui-button, ui-input, ui-label, ui-textarea
```

**Asset preload headers** (when Vite integration is enabled):
```
Link: </build/assets/app-BhF9KQ3W.js>; rel=preload; as=script
Link: </build/assets/app-CJGkQYKR.css>; rel=preload; as=style
```

This allows reverse proxies, CDNs, or HTTP/2-capable servers to push assets before the browser requests them.

## Form Theme

The form theme is the core feature of this bundle. It overrides Symfony's default form rendering blocks so that forms automatically use Reactolith `<ui-*>` tags.

### Supported Form Type Mappings

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

For form types not listed above (e.g., `DateType`, `DateTimeType`, `ColorType`), the bundle falls back to Symfony's standard `form_div_layout.html.twig` rendering.

### Form Row Structure

Each form row is wrapped in a consistent layout:

```html
<div class="space-y-2">
  <ui-label for="...">Label</ui-label>
  <!-- widget -->
  <p class="text-sm text-muted-foreground">Help text</p>
  <p class="text-sm font-medium text-destructive">Error message</p>
</div>
```

### Custom Tag Prefix

The tag prefix is configurable. If you set `tag_prefix: 'x-'`, all tags will use that prefix:

```html
<x-input type="text" ... />
<x-button type="submit">Submit</x-button>
```

### SwitchType

The bundle provides a custom `SwitchType` form type that renders as `<ui-switch>`:

```php
use Reactolith\SymfonyBundle\Form\Type\SwitchType;

$builder->add('darkMode', SwitchType::class, [
    'label' => 'Enable dark mode',
]);
```

Output:

```html
<ui-switch id="form_darkMode" name="form[darkMode]" json-checked="false" />
```

`SwitchType` extends `CheckboxType`, so it accepts the same options and submits a boolean value.

## Frontend Setup

This bundle only provides the **server-side** integration. You also need the Reactolith client-side libraries to hydrate the HTML into React components.

### 1. Install Reactolith

```bash
npm install reactolith @loadable/component
```

### 2. Install UI Components

Use the [Reactolith UI Registry](https://github.com/reactolith/ui) (shadcn-compatible) to install the components your forms need:

```bash
npx shadcn add @reactolith/button @reactolith/input @reactolith/select \
  @reactolith/checkbox @reactolith/switch @reactolith/radio-group \
  @reactolith/label @reactolith/textarea
```

Components are installed locally into your project under `components/reactolith/`.

### 3. Set Up Vite

Configure Vite with a standard `vite.config.js` that includes your entry point. The bundle reads Vite's manifest for production builds and connects to the dev server during development.

Refer to the [Reactolith documentation](https://github.com/reactolith/reactolith) for the full client-side setup.

## Customizing the Form Theme

### Overriding Specific Blocks

You can override individual form blocks using Symfony's standard mechanisms:

```twig
{% form_theme form _self %}

{% block form_widget_simple %}
  {# Your custom input rendering #}
  <custom-input type="{{ type|default('text') }}" name="{{ full_name }}" />
{% endblock %}

{{ form(form) }}
```

### Creating a Custom Theme

Create your own theme that extends the Reactolith theme:

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

Then apply it globally in `config/packages/twig.yaml`:

```yaml
twig:
  form_themes:
    - 'form/my_theme.html.twig'
```

Or per form:

```twig
{% form_theme form 'form/my_theme.html.twig' %}
{{ form(form) }}
```

### Disabling the Global Form Theme

If you prefer to apply the form theme manually per form instead of globally:

```yaml
reactolith:
  form_theme:
    enabled: false
```

Then apply it in your templates:

```twig
{% form_theme form '@Reactolith/form/reactolith_layout.html.twig' %}
{{ form(form) }}
```

## License

MIT License. See [LICENSE](LICENSE) for details.
