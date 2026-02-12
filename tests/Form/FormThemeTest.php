<?php

namespace Reactolith\SymfonyBundle\Tests\Form;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\Form\Type\SwitchType;
use Reactolith\SymfonyBundle\Twig\ReactolithTwigExtension;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Forms;
use Symfony\Component\Translation\IdentityTranslator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

class FormThemeTest extends TestCase
{
    private Environment $twig;
    private FormRenderer $renderer;
    private $formFactory;

    protected function setUp(): void
    {
        $loader = new FilesystemLoader([]);

        // Add the Symfony Bridge form templates directory
        $bridgeDir = dirname((new \ReflectionClass(FormExtension::class))->getFileName());
        $formResourceDir = realpath($bridgeDir . '/../Resources/views/Form');
        if ($formResourceDir) {
            $loader->addPath($formResourceDir);
        }

        // Add our bundle's templates directory
        $bundleTemplatesDir = dirname(__DIR__, 2) . '/templates';
        $loader->addPath($bundleTemplatesDir, 'Reactolith');

        $this->twig = new Environment($loader, [
            'strict_variables' => true,
        ]);

        // Add Twig globals to simulate what PrependExtensionInterface would do
        $this->twig->addGlobal('reactolith_tag_prefix', 'ui-');

        // Add translation extension (needed for form rendering)
        $this->twig->addExtension(new TranslationExtension(new IdentityTranslator()));

        // Add Reactolith Twig extension
        $this->twig->addExtension(new ReactolithTwigExtension());

        // Set up the form renderer engine
        $rendererEngine = new TwigRendererEngine([
            'form_div_layout.html.twig',
            '@Reactolith/form/reactolith_layout.html.twig',
        ], $this->twig);

        $this->renderer = new FormRenderer($rendererEngine);

        // Add form extension with runtime loader
        $this->twig->addExtension(new FormExtension());
        $renderer = $this->renderer;
        $this->twig->addRuntimeLoader(new class($renderer) implements RuntimeLoaderInterface {
            private FormRenderer $renderer;

            public function __construct(FormRenderer $renderer)
            {
                $this->renderer = $renderer;
            }

            public function load(string $class): ?object
            {
                if ($class === FormRenderer::class) {
                    return $this->renderer;
                }

                return null;
            }
        });

        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addType(new SwitchType())
            ->getFormFactory();
    }

    public function testTextTypeRendersAsUiInput(): void
    {
        $form = $this->formFactory->createBuilder()
            ->add('name', TextType::class)
            ->getForm();

        $html = $this->renderWidget($form->get('name'));

        $this->assertStringContainsString('<ui-input', $html);
        $this->assertStringContainsString('type="text"', $html);
    }

    public function testEmailTypeRendersAsUiInput(): void
    {
        $form = $this->formFactory->createBuilder()
            ->add('email', EmailType::class)
            ->getForm();

        $html = $this->renderWidget($form->get('email'));

        $this->assertStringContainsString('<ui-input', $html);
        $this->assertStringContainsString('type="email"', $html);
    }

    public function testTextareaTypeRendersAsUiTextarea(): void
    {
        $form = $this->formFactory->createBuilder()
            ->add('message', TextareaType::class)
            ->getForm();

        $html = $this->renderWidget($form->get('message'));

        $this->assertStringContainsString('<ui-textarea', $html);
        $this->assertStringContainsString('</ui-textarea>', $html);
    }

    public function testChoiceCollapsedRendersAsUiSelect(): void
    {
        $form = $this->formFactory->createBuilder()
            ->add('color', ChoiceType::class, [
                'choices' => [
                    'Red' => 'red',
                    'Blue' => 'blue',
                    'Green' => 'green',
                ],
            ])
            ->getForm();

        $html = $this->renderWidget($form->get('color'));

        $this->assertStringContainsString('<ui-select', $html);
        $this->assertStringContainsString('<ui-select-item', $html);
        $this->assertStringContainsString('value="red"', $html);
        $this->assertStringContainsString('value="blue"', $html);
        $this->assertStringContainsString('value="green"', $html);
    }

    public function testChoiceExpandedRadioRendersAsUiRadioGroup(): void
    {
        $form = $this->formFactory->createBuilder()
            ->add('size', ChoiceType::class, [
                'choices' => [
                    'Small' => 'sm',
                    'Large' => 'lg',
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->getForm();

        $html = $this->renderWidget($form->get('size'));

        $this->assertStringContainsString('<ui-radio-group', $html);
        $this->assertStringContainsString('<ui-radio-group-item', $html);
    }

    public function testChoiceExpandedCheckboxRendersAsUiCheckbox(): void
    {
        $form = $this->formFactory->createBuilder()
            ->add('features', ChoiceType::class, [
                'choices' => [
                    'Feature A' => 'a',
                    'Feature B' => 'b',
                ],
                'expanded' => true,
                'multiple' => true,
            ])
            ->getForm();

        $html = $this->renderWidget($form->get('features'));

        $this->assertStringContainsString('<ui-checkbox', $html);
    }

    public function testCheckboxTypeRendersAsUiCheckbox(): void
    {
        $form = $this->formFactory->createBuilder()
            ->add('agree', CheckboxType::class)
            ->getForm();

        $html = $this->renderWidget($form->get('agree'));

        $this->assertStringContainsString('<ui-checkbox', $html);
        $this->assertStringContainsString('json-checked=', $html);
    }

    public function testSwitchTypeRendersAsUiSwitch(): void
    {
        $form = $this->formFactory->createBuilder()
            ->add('notifications', SwitchType::class)
            ->getForm();

        $html = $this->renderWidget($form->get('notifications'));

        $this->assertStringContainsString('<ui-switch', $html);
        $this->assertStringContainsString('json-checked=', $html);
    }

    public function testSubmitTypeRendersAsUiButton(): void
    {
        $form = $this->formFactory->createBuilder()
            ->add('send', SubmitType::class)
            ->getForm();

        $html = $this->renderWidget($form->get('send'));

        $this->assertStringContainsString('<ui-button', $html);
        $this->assertStringContainsString('type="submit"', $html);
    }

    public function testLabelsRenderAsUiLabel(): void
    {
        $form = $this->formFactory->createBuilder()
            ->add('name', TextType::class, ['label' => 'Your Name'])
            ->getForm();

        $html = $this->renderLabel($form->get('name'));

        $this->assertStringContainsString('<ui-label', $html);
        $this->assertStringContainsString('Your Name', $html);
        $this->assertStringContainsString('</ui-label>', $html);
    }

    public function testErrorsRenderWithCorrectCssClass(): void
    {
        $form = $this->formFactory->createBuilder()
            ->add('name', TextType::class)
            ->getForm();

        // Submit with invalid data to trigger errors
        $form->submit([]);
        $form->get('name')->addError(new \Symfony\Component\Form\FormError('This field is required'));

        $html = $this->renderErrors($form->get('name'));

        $this->assertStringContainsString('text-sm font-medium text-destructive', $html);
        $this->assertStringContainsString('This field is required', $html);
    }

    public function testHelpTextRendersCorrectly(): void
    {
        $form = $this->formFactory->createBuilder()
            ->add('email', EmailType::class, [
                'help' => 'Enter your primary email address',
            ])
            ->getForm();

        $html = $this->renderHelp($form->get('email'));

        $this->assertStringContainsString('text-sm text-muted-foreground', $html);
        $this->assertStringContainsString('Enter your primary email address', $html);
    }

    private function renderWidget($formView): string
    {
        $view = $formView->createView();

        return $this->renderer->searchAndRenderBlock($view, 'widget');
    }

    private function renderLabel($formView): string
    {
        $view = $formView->createView();

        return $this->renderer->searchAndRenderBlock($view, 'label');
    }

    private function renderErrors($formView): string
    {
        $view = $formView->createView();

        return $this->renderer->searchAndRenderBlock($view, 'errors');
    }

    private function renderHelp($formView): string
    {
        $view = $formView->createView();

        return $this->renderer->searchAndRenderBlock($view, 'help');
    }
}
