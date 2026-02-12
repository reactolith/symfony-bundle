# Reactolith Symfony Bundle

Symfony integration for [Reactolith](https://github.com/reactolith/reactolith) – server-side HTML hydration with React/shadcn components.

**Write your UI in Twig. Get React-powered shadcn/ui components. No JavaScript in your Symfony code.**

Reactolith lets you render HTML with custom tags like `<ui-button>`, `<ui-input>`, `<ui-select>` from your Symfony backend. On the client side, Reactolith automatically hydrates these tags into fully interactive React components (based on [shadcn/ui](https://ui.shadcn.com/)). This bundle provides the Symfony-side integration:

- **Twig Form Theme** – Symfony forms automatically render `<ui-*>` HTML tags
- **Twig Extension** – Helper functions for Reactolith (root element, Mercure config, prop rendering)
- **Mercure Integration** – Auto-configuration of the Mercure Hub URL for real-time updates
- **Custom FormTypes** – Additional form types like `SwitchType`

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
{{ reactolith_root_open() }}
  <h1>Contact Us</h1>
  {{ form(form) }}
{{ reactolith_root_close() }}
```

### 3. Resulting HTML Output

The form theme automatically transforms Symfony's standard form rendering into Reactolith-compatible HTML:

```html
<div id="reactolith">
  <h1>Contact Us</h1>
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
</div>
```

Reactolith on the client side hydrates each `<ui-*>` tag into its corresponding React/shadcn component, giving you fully interactive, styled UI components without writing any JavaScript.

## Configuration

Create `config/packages/reactolith.yaml`:

```yaml
reactolith:
  # CSS selector for the Reactolith root element
  root_selector: '#reactolith'

  # HTML tag prefix for components (e.g. "ui-" -> <ui-button>, <ui-input>)
  tag_prefix: 'ui-'

  # Mercure configuration (optional, auto-detected from MercureBundle if available)
  mercure:
    enabled: true           # false to disable Mercure integration
    hub_url: ~              # auto-detected from symfony/mercure-bundle, or set manually
    with_credentials: false # whether to send cookies with Mercure requests

  # Form theme
  form_theme:
    enabled: true           # auto-registers the form theme globally
```

### Configuration Options

| Option | Default | Description |
|--------|---------|-------------|
| `root_selector` | `#reactolith` | CSS selector for the Reactolith root element. Used to derive the default `id` attribute. |
| `tag_prefix` | `ui-` | HTML tag prefix for all Reactolith components. Change this if `ui-` conflicts with your project. |
| `mercure.enabled` | `true` | Whether to include Mercure data attributes on the root element. |
| `mercure.hub_url` | `null` | Mercure Hub URL. Auto-detected from `symfony/mercure-bundle` if installed, or set manually. |
| `mercure.with_credentials` | `false` | Whether Mercure requests should include cookies (for authenticated SSE connections). |
| `form_theme.enabled` | `true` | Whether to auto-register the Reactolith form theme globally. Disable if you want to apply it manually per form. |

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

## Twig Functions

### `reactolith_root_open(options = {})`

Renders the opening tag of the Reactolith root element:

```twig
{{ reactolith_root_open() }}
{# Output: <div id="reactolith"> #}

{{ reactolith_root_open({ id: 'my-app', class: 'min-h-screen' }) }}
{# Output: <div id="my-app" class="min-h-screen"> #}
```

If Mercure is configured, the root element includes Mercure data attributes:

```html
<div id="reactolith" data-mercure-hub-url="https://example.com/.well-known/mercure" data-mercure-with-credentials>
```

### `reactolith_root_close()`

Renders the closing tag:

```twig
{{ reactolith_root_close() }}
{# Output: </div> #}
```

### `reactolith_attr(name, value)`

Helper to render Reactolith props with correct formatting based on value type:

```twig
{# String value: normal HTML attribute #}
{{ reactolith_attr('variant', 'outline') }}
{# Output: variant="outline" #}

{# Boolean true: attribute without value #}
{{ reactolith_attr('disabled', true) }}
{# Output: disabled #}

{# Boolean false: attribute omitted #}
{{ reactolith_attr('disabled', false) }}
{# Output: (empty string) #}

{# Array/Object: json- prefix with JSON-encoded value #}
{{ reactolith_attr('config', { theme: 'dark', size: 'lg' }) }}
{# Output: json-config='{"theme":"dark","size":"lg"}' #}
```

**Prop rendering rules (Reactolith conventions):**

| Value Type | Output Format | Example |
|-----------|--------------|---------|
| String | `name="value"` | `variant="outline"` |
| Boolean `true` | `name` (no value) | `disabled` |
| Boolean `false` | *(omitted)* | |
| Array/Object | `json-name='...'` | `json-config='{"foo":"bar"}'` |
| Component ref | `as-name="component-tag"` | `as-icon="ui-chevron-down"` |

## Mercure Integration

The bundle automatically detects the Mercure Hub URL when `symfony/mercure-bundle` is installed:

```bash
composer require symfony/mercure-bundle
```

The Hub URL is injected into the root element as a data attribute, which Reactolith's client-side library uses to establish an SSE connection for real-time updates.

### Auto-Detection

If `symfony/mercure-bundle` is installed and configured, the Hub URL is automatically detected. No additional configuration is needed.

### Manual Configuration

You can also set the Hub URL manually:

```yaml
reactolith:
  mercure:
    hub_url: 'https://mercure.example.com/.well-known/mercure'
    with_credentials: true
```

### Disabling Mercure

```yaml
reactolith:
  mercure:
    enabled: false
```

## Frontend Setup

This bundle only provides the **server-side** integration. You also need the Reactolith client-side libraries to hydrate the HTML into React components.

### 1. Install Reactolith

```bash
npm install reactolith @loadable/component
```

### 2. Install UI Components

Use the [Reactolith UI Registry](https://github.com/reactolith/ui) (shadcn-compatible) to install the components your forms need:

```bash
npx shadcn add @reactolith/button @reactolith/input @reactolith/select @reactolith/checkbox @reactolith/switch @reactolith/radio-group @reactolith/label @reactolith/textarea
```

Components are installed locally into your project under `components/reactolith/`.

### 3. Set Up the Loader

Configure the Reactolith loader in your JavaScript entry point to register all installed components. Refer to the [Reactolith documentation](https://github.com/reactolith/reactolith) for setup instructions.

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

Then apply it globally:

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

## SwitchType

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

## License

MIT License. See [LICENSE](LICENSE) for details.
