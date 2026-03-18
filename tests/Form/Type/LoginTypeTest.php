<?php

namespace Reactolith\SymfonyBundle\Tests\Form\Type;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\Form\Type\CardFormType;
use Reactolith\SymfonyBundle\Form\Type\LoginType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Forms;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginTypeTest extends TestCase
{
    private LoginType $type;

    protected function setUp(): void
    {
        $this->type = new LoginType();
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertSame('reactolith_login', $this->type->getBlockPrefix());
    }

    public function testGetParentReturnsCardFormType(): void
    {
        $this->assertSame(CardFormType::class, $this->type->getParent());
    }



    public function testDefaultOptions(): void
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined([
            'card_title', 'card_description', 'card_footer_text',
            'card_footer_link_label', 'card_footer_link_url',
            'social_providers', 'forgot_password_url', 'signup_url',
            'remember_me', 'csrf_protection',
        ]);

        $this->type->configureOptions($resolver);

        $resolved = $resolver->resolve([]);
        $this->assertSame('Login', $resolved['card_title']);
        $this->assertSame('Enter your email below to login to your account', $resolved['card_description']);
        $this->assertNull($resolved['forgot_password_url']);
        $this->assertNull($resolved['signup_url']);
        $this->assertFalse($resolved['remember_me']);
    }

    public function testSignupUrlSetsCardFooter(): void
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined([
            'card_title', 'card_description', 'card_footer_text',
            'card_footer_link_label', 'card_footer_link_url',
            'social_providers', 'forgot_password_url', 'signup_url',
            'remember_me', 'csrf_protection',
        ]);

        $this->type->configureOptions($resolver);

        $resolved = $resolver->resolve(['signup_url' => '/register']);
        $this->assertSame("Don't have an account?", $resolved['card_footer_text']);
        $this->assertSame('Sign up', $resolved['card_footer_link_label']);
        $this->assertSame('/register', $resolved['card_footer_link_url']);
    }

    public function testBuildFormAddsEmailAndPasswordAndSubmit(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new LoginType())
            ->getFormFactory();

        $form = $formFactory->create(LoginType::class);

        $this->assertTrue($form->has('email'));
        $this->assertTrue($form->has('password'));
        $this->assertTrue($form->has('submit'));
    }

    public function testEmailFieldIsEmailType(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new LoginType())
            ->getFormFactory();

        $form = $formFactory->create(LoginType::class);

        $this->assertInstanceOf(
            EmailType::class,
            $form->get('email')->getConfig()->getType()->getInnerType()
        );
    }

    public function testPasswordFieldIsPasswordType(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new LoginType())
            ->getFormFactory();

        $form = $formFactory->create(LoginType::class);

        $this->assertInstanceOf(
            PasswordType::class,
            $form->get('password')->getConfig()->getType()->getInnerType()
        );
    }

    public function testRememberMeFieldNotAddedByDefault(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new LoginType())
            ->getFormFactory();

        $form = $formFactory->create(LoginType::class);

        $this->assertFalse($form->has('remember_me'));
    }

    public function testRememberMeFieldAddedWhenEnabled(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new LoginType())
            ->getFormFactory();

        $form = $formFactory->create(LoginType::class, null, ['remember_me' => true]);

        $this->assertTrue($form->has('remember_me'));
        $this->assertInstanceOf(
            CheckboxType::class,
            $form->get('remember_me')->getConfig()->getType()->getInnerType()
        );
    }

    public function testSubmitFieldIsSubmitType(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new LoginType())
            ->getFormFactory();

        $form = $formFactory->create(LoginType::class);

        $this->assertInstanceOf(
            SubmitType::class,
            $form->get('submit')->getConfig()->getType()->getInnerType()
        );
    }

    public function testEmailHasAutocompleteAttribute(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new LoginType())
            ->getFormFactory();

        $form = $formFactory->create(LoginType::class);

        $attr = $form->get('email')->getConfig()->getOption('attr');
        $this->assertSame('email', $attr['autocomplete']);
    }

    public function testPasswordHasAutocompleteAttribute(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new LoginType())
            ->getFormFactory();

        $form = $formFactory->create(LoginType::class);

        $attr = $form->get('password')->getConfig()->getOption('attr');
        $this->assertSame('current-password', $attr['autocomplete']);
    }

    public function testForgotPasswordUrlPassedToView(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new LoginType())
            ->getFormFactory();

        $form = $formFactory->create(LoginType::class, null, [
            'forgot_password_url' => '/reset-password',
        ]);

        $view = $form->createView();

        // The forgot_password_url should be available in the password field's view vars
        $this->assertSame('/reset-password', $view['password']->vars['label_link_url']);
        $this->assertNotEmpty($view['password']->vars['label_link_label']);
    }
}
