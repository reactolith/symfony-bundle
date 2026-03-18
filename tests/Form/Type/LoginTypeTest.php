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

class LoginTypeTest extends TestCase
{
    private LoginType $type;
    private $formFactory;

    protected function setUp(): void
    {
        $this->type = new LoginType();
        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new LoginType())
            ->getFormFactory();
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
        $form = $this->formFactory->create(LoginType::class);
        $options = $form->getConfig()->getOptions();

        $this->assertSame('Login', $options['card_title']);
        $this->assertSame('Enter your email below to login to your account', $options['card_description']);
        $this->assertNull($options['forgot_password_url']);
        $this->assertNull($options['signup_url']);
        $this->assertFalse($options['remember_me']);
    }

    public function testSignupUrlSetsFooterInView(): void
    {
        $form = $this->formFactory->create(LoginType::class, null, [
            'signup_url' => '/register',
        ]);
        $view = $form->createView();

        $this->assertSame("Don't have an account?", $view->vars['card_footer_text']);
        $this->assertSame('Sign up', $view->vars['card_footer_link_label']);
        $this->assertSame('/register', $view->vars['card_footer_link_url']);
    }

    public function testNoFooterWithoutSignupUrl(): void
    {
        $form = $this->formFactory->create(LoginType::class);
        $view = $form->createView();

        $this->assertNull($view->vars['card_footer_text']);
    }

    public function testBuildFormAddsEmailAndPasswordAndSubmit(): void
    {
        $form = $this->formFactory->create(LoginType::class);

        $this->assertTrue($form->has('email'));
        $this->assertTrue($form->has('password'));
        $this->assertTrue($form->has('submit'));
    }

    public function testEmailFieldIsEmailType(): void
    {
        $form = $this->formFactory->create(LoginType::class);

        $this->assertInstanceOf(
            EmailType::class,
            $form->get('email')->getConfig()->getType()->getInnerType()
        );
    }

    public function testPasswordFieldIsPasswordType(): void
    {
        $form = $this->formFactory->create(LoginType::class);

        $this->assertInstanceOf(
            PasswordType::class,
            $form->get('password')->getConfig()->getType()->getInnerType()
        );
    }

    public function testRememberMeFieldNotAddedByDefault(): void
    {
        $form = $this->formFactory->create(LoginType::class);

        $this->assertFalse($form->has('remember_me'));
    }

    public function testRememberMeFieldAddedWhenEnabled(): void
    {
        $form = $this->formFactory->create(LoginType::class, null, ['remember_me' => true]);

        $this->assertTrue($form->has('remember_me'));
        $this->assertInstanceOf(
            CheckboxType::class,
            $form->get('remember_me')->getConfig()->getType()->getInnerType()
        );
    }

    public function testSubmitFieldIsSubmitType(): void
    {
        $form = $this->formFactory->create(LoginType::class);

        $this->assertInstanceOf(
            SubmitType::class,
            $form->get('submit')->getConfig()->getType()->getInnerType()
        );
    }

    public function testEmailHasAutocompleteAttribute(): void
    {
        $form = $this->formFactory->create(LoginType::class);

        $attr = $form->get('email')->getConfig()->getOption('attr');
        $this->assertSame('email', $attr['autocomplete']);
    }

    public function testPasswordHasAutocompleteAttribute(): void
    {
        $form = $this->formFactory->create(LoginType::class);

        $attr = $form->get('password')->getConfig()->getOption('attr');
        $this->assertSame('current-password', $attr['autocomplete']);
    }

    public function testForgotPasswordUrlPassedToView(): void
    {
        $form = $this->formFactory->create(LoginType::class, null, [
            'forgot_password_url' => '/reset-password',
        ]);

        $view = $form->createView();

        $this->assertSame('/reset-password', $view['password']->vars['label_link_url']);
        $this->assertNotEmpty($view['password']->vars['label_link_label']);
    }
}
