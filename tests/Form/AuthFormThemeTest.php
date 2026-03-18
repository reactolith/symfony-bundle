<?php

namespace Reactolith\SymfonyBundle\Tests\Form;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\Form\Type\AccountActivationType;
use Reactolith\SymfonyBundle\Form\Type\CardFormType;
use Reactolith\SymfonyBundle\Form\Type\FieldGroupType;
use Reactolith\SymfonyBundle\Form\Type\LoginType;
use Reactolith\SymfonyBundle\Form\Type\PasswordResetConfirmType;
use Reactolith\SymfonyBundle\Form\Type\PasswordResetRequestType;
use Reactolith\SymfonyBundle\Form\Type\SeparatorType;
use Reactolith\SymfonyBundle\Form\Type\SignupType;
use Reactolith\SymfonyBundle\Form\Type\SwitchType;
use Reactolith\SymfonyBundle\Twig\ReactolithTwigExtension;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Forms;
use Symfony\Component\Translation\IdentityTranslator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

class AuthFormThemeTest extends TestCase
{
    private Environment $twig;
    private FormRenderer $renderer;
    private $formFactory;

    protected function setUp(): void
    {
        $loader = new FilesystemLoader([]);

        $bridgeDir = dirname((new \ReflectionClass(FormExtension::class))->getFileName());
        $formResourceDir = realpath($bridgeDir . '/../Resources/views/Form');
        if ($formResourceDir) {
            $loader->addPath($formResourceDir);
        }

        $bundleTemplatesDir = dirname(__DIR__, 2) . '/templates';
        $loader->addPath($bundleTemplatesDir, 'Reactolith');

        $this->twig = new Environment($loader, [
            'strict_variables' => true,
        ]);

        $this->twig->addGlobal('reactolith_tag_prefix', 'ui-');
        $this->twig->addExtension(new TranslationExtension(new IdentityTranslator()));
        $this->twig->addExtension(new ReactolithTwigExtension());

        $rendererEngine = new TwigRendererEngine([
            'form_div_layout.html.twig',
            '@Reactolith/form/reactolith_layout.html.twig',
        ], $this->twig);

        $this->renderer = new FormRenderer($rendererEngine);

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
            ->addType(new CardFormType())
            ->addType(new FieldGroupType())
            ->addType(new SeparatorType())
            ->addType(new LoginType())
            ->addType(new SignupType())
            ->addType(new PasswordResetRequestType())
            ->addType(new PasswordResetConfirmType())
            ->addType(new AccountActivationType())
            ->getFormFactory();
    }

    // ──────────────────────────────────────────────────
    // LoginType rendering
    // ──────────────────────────────────────────────────

    public function testLoginFormRendersCardWrapper(): void
    {
        $form = $this->formFactory->create(LoginType::class);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('<ui-card', $html);
        $this->assertStringContainsString('</ui-card>', $html);
    }

    public function testLoginFormRendersCardTitle(): void
    {
        $form = $this->formFactory->create(LoginType::class);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('<ui-card-title>', $html);
        $this->assertStringContainsString('Login', $html);
    }

    public function testLoginFormRendersCardDescription(): void
    {
        $form = $this->formFactory->create(LoginType::class);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('<ui-card-description>', $html);
        $this->assertStringContainsString('Enter your email below to login to your account', $html);
    }

    public function testLoginFormRendersCardContent(): void
    {
        $form = $this->formFactory->create(LoginType::class);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('<ui-card-content>', $html);
        $this->assertStringContainsString('</ui-card-content>', $html);
    }

    public function testLoginFormRendersEmailInput(): void
    {
        $form = $this->formFactory->create(LoginType::class);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('<ui-input', $html);
        $this->assertStringContainsString('type="email"', $html);
    }

    public function testLoginFormRendersPasswordInput(): void
    {
        $form = $this->formFactory->create(LoginType::class);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('type="password"', $html);
    }

    public function testLoginFormRendersSubmitButton(): void
    {
        $form = $this->formFactory->create(LoginType::class);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('<ui-button', $html);
        $this->assertStringContainsString('type="submit"', $html);
        $this->assertStringContainsString('Login', $html);
    }

    public function testLoginFormRendersCardFooterWithSignupUrl(): void
    {
        $form = $this->formFactory->create(LoginType::class, null, [
            'signup_url' => '/register',
        ]);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('<ui-card-footer>', $html);
        $this->assertStringContainsString("have an account?", $html);
        $this->assertStringContainsString('href="/register"', $html);
        $this->assertStringContainsString('Sign up', $html);
    }

    public function testLoginFormNoFooterWithoutSignupUrl(): void
    {
        $form = $this->formFactory->create(LoginType::class);
        $html = $this->renderForm($form);

        $this->assertStringNotContainsString('<ui-card-footer>', $html);
    }

    public function testLoginFormRendersForgotPasswordLink(): void
    {
        $form = $this->formFactory->create(LoginType::class, null, [
            'forgot_password_url' => '/reset-password',
        ]);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('href="/reset-password"', $html);
        $this->assertStringContainsString('Forgot your password?', $html);
    }

    public function testLoginFormRendersRememberMe(): void
    {
        $form = $this->formFactory->create(LoginType::class, null, [
            'remember_me' => true,
        ]);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('<ui-checkbox', $html);
        $this->assertStringContainsString('Remember me', $html);
    }

    public function testLoginFormWithCustomTitle(): void
    {
        $form = $this->formFactory->create(LoginType::class, null, [
            'card_title' => 'Welcome back',
        ]);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('Welcome back', $html);
    }

    // ──────────────────────────────────────────────────
    // LoginType with social login
    // ──────────────────────────────────────────────────

    public function testLoginFormRendersSocialProviders(): void
    {
        $form = $this->formFactory->create(LoginType::class, null, [
            'social_providers' => [
                ['name' => 'Google', 'url' => '/connect/google'],
                ['name' => 'GitHub', 'url' => '/connect/github'],
            ],
        ]);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('<ui-separator', $html);
        $this->assertStringContainsString('Or continue with', $html);
        $this->assertStringContainsString('href="/connect/google"', $html);
        $this->assertStringContainsString('Google', $html);
        $this->assertStringContainsString('href="/connect/github"', $html);
        $this->assertStringContainsString('GitHub', $html);
    }

    public function testLoginFormNoSocialSectionWithoutProviders(): void
    {
        $form = $this->formFactory->create(LoginType::class);
        $html = $this->renderForm($form);

        $this->assertStringNotContainsString('Or continue with', $html);
    }

    // ──────────────────────────────────────────────────
    // SignupType rendering
    // ──────────────────────────────────────────────────

    public function testSignupFormRendersCardWrapper(): void
    {
        $form = $this->formFactory->create(SignupType::class);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('<ui-card', $html);
        $this->assertStringContainsString('Create an account', $html);
    }

    public function testSignupFormRendersEmailField(): void
    {
        $form = $this->formFactory->create(SignupType::class);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('type="email"', $html);
    }

    public function testSignupFormRendersPasswordFields(): void
    {
        $form = $this->formFactory->create(SignupType::class);
        $html = $this->renderForm($form);

        // RepeatedType creates two password fields
        $passwordCount = substr_count($html, 'type="password"');
        $this->assertSame(2, $passwordCount);
    }

    public function testSignupFormRendersAgreeTermsCheckbox(): void
    {
        $form = $this->formFactory->create(SignupType::class);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('<ui-checkbox', $html);
    }

    public function testSignupFormRendersFooterWithLoginUrl(): void
    {
        $form = $this->formFactory->create(SignupType::class, null, [
            'login_url' => '/login',
        ]);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('Already have an account?', $html);
        $this->assertStringContainsString('href="/login"', $html);
    }

    // ──────────────────────────────────────────────────
    // PasswordResetRequestType rendering
    // ──────────────────────────────────────────────────

    public function testPasswordResetRequestFormRendersCardWrapper(): void
    {
        $form = $this->formFactory->create(PasswordResetRequestType::class);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('<ui-card', $html);
        $this->assertStringContainsString('Reset password', $html);
    }

    public function testPasswordResetRequestFormRendersEmailOnly(): void
    {
        $form = $this->formFactory->create(PasswordResetRequestType::class);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('type="email"', $html);
        $this->assertStringNotContainsString('type="password"', $html);
    }

    // ──────────────────────────────────────────────────
    // PasswordResetConfirmType rendering
    // ──────────────────────────────────────────────────

    public function testPasswordResetConfirmFormRendersCardWrapper(): void
    {
        $form = $this->formFactory->create(PasswordResetConfirmType::class);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('<ui-card', $html);
        $this->assertStringContainsString('Set new password', $html);
    }

    public function testPasswordResetConfirmFormRendersTwoPasswordFields(): void
    {
        $form = $this->formFactory->create(PasswordResetConfirmType::class);
        $html = $this->renderForm($form);

        $passwordCount = substr_count($html, 'type="password"');
        $this->assertSame(2, $passwordCount);
    }

    // ──────────────────────────────────────────────────
    // AccountActivationType rendering
    // ──────────────────────────────────────────────────

    public function testAccountActivationFormRendersCardWrapper(): void
    {
        $form = $this->formFactory->create(AccountActivationType::class);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('<ui-card', $html);
        $this->assertStringContainsString('Activate your account', $html);
    }

    public function testAccountActivationFormRendersSubmitOnly(): void
    {
        $form = $this->formFactory->create(AccountActivationType::class);
        $html = $this->renderForm($form);

        $this->assertStringContainsString('<ui-button', $html);
        $this->assertStringContainsString('type="submit"', $html);
        // Should not have email or password fields
        $this->assertStringNotContainsString('type="email"', $html);
        $this->assertStringNotContainsString('type="password"', $html);
    }

    // ──────────────────────────────────────────────────
    // FieldGroupType rendering
    // ──────────────────────────────────────────────────

    public function testFieldGroupRendersAsFlexColumn(): void
    {
        $form = $this->formFactory->createBuilder()
            ->add('group', FieldGroupType::class)
            ->getForm();

        $html = $this->renderWidget($form->get('group'));

        $this->assertStringContainsString('flex flex-col', $html);
        $this->assertStringContainsString('gap-4', $html);
    }

    public function testFieldGroupRendersAsFlexRowWhenConfigured(): void
    {
        $form = $this->formFactory->createBuilder()
            ->add('group', FieldGroupType::class, ['row' => true])
            ->getForm();

        $html = $this->renderWidget($form->get('group'));

        $this->assertStringContainsString('flex flex-row', $html);
    }

    public function testFieldGroupRendersCustomGap(): void
    {
        $form = $this->formFactory->createBuilder()
            ->add('group', FieldGroupType::class, ['gap' => '2'])
            ->getForm();

        $html = $this->renderWidget($form->get('group'));

        $this->assertStringContainsString('gap-2', $html);
    }

    // ──────────────────────────────────────────────────
    // SeparatorType rendering
    // ──────────────────────────────────────────────────

    public function testSeparatorRendersUiSeparator(): void
    {
        $form = $this->formFactory->createBuilder()
            ->add('divider', SeparatorType::class)
            ->getForm();

        $html = $this->renderRow($form->get('divider'));

        $this->assertStringContainsString('<ui-separator', $html);
    }

    public function testSeparatorRendersLabelText(): void
    {
        $form = $this->formFactory->createBuilder()
            ->add('divider', SeparatorType::class, ['label' => 'Or continue with'])
            ->getForm();

        $html = $this->renderRow($form->get('divider'));

        $this->assertStringContainsString('Or continue with', $html);
    }

    // ──────────────────────────────────────────────────
    // Label link rendering
    // ──────────────────────────────────────────────────

    public function testLabelLinkRendersInLabelRow(): void
    {
        $form = $this->formFactory->create(LoginType::class, null, [
            'forgot_password_url' => '/forgot',
        ]);
        $view = $form->createView();
        $html = $this->renderer->searchAndRenderBlock($view['password'], 'label');

        $this->assertStringContainsString('Password', $html);
        $this->assertStringContainsString('href="/forgot"', $html);
        $this->assertStringContainsString('Forgot your password?', $html);
    }

    // ──────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────

    private function renderForm($form): string
    {
        $view = $form->createView();

        return $this->renderer->searchAndRenderBlock($view, 'start')
            . $this->renderer->searchAndRenderBlock($view, 'widget')
            . $this->renderer->searchAndRenderBlock($view, 'end');
    }

    private function renderWidget($formView): string
    {
        $view = $formView->createView();

        return $this->renderer->searchAndRenderBlock($view, 'widget');
    }

    private function renderRow($formView): string
    {
        $view = $formView->createView();

        return $this->renderer->searchAndRenderBlock($view, 'row');
    }
}
