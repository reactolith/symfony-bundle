<?php

namespace Reactolith\SymfonyBundle\Tests\Form\Type;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\Form\Type\CardFormType;
use Reactolith\SymfonyBundle\Form\Type\SignupType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Forms;

class SignupTypeTest extends TestCase
{
    private SignupType $type;
    private $formFactory;

    protected function setUp(): void
    {
        $this->type = new SignupType();
        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new SignupType())
            ->getFormFactory();
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertSame('reactolith_signup', $this->type->getBlockPrefix());
    }

    public function testGetParentReturnsCardFormType(): void
    {
        $this->assertSame(CardFormType::class, $this->type->getParent());
    }

    public function testDefaultOptions(): void
    {
        $form = $this->formFactory->create(SignupType::class);
        $options = $form->getConfig()->getOptions();

        $this->assertSame('Create an account', $options['card_title']);
        $this->assertSame('Enter your details below to create your account', $options['card_description']);
        $this->assertNull($options['login_url']);
        $this->assertNull($options['terms_url']);
    }

    public function testLoginUrlSetsFooterInView(): void
    {
        $form = $this->formFactory->create(SignupType::class, null, [
            'login_url' => '/login',
        ]);
        $view = $form->createView();

        $this->assertSame('Already have an account?', $view->vars['card_footer_text']);
        $this->assertSame('Login', $view->vars['card_footer_link_label']);
        $this->assertSame('/login', $view->vars['card_footer_link_url']);
    }

    public function testBuildFormAddsExpectedFields(): void
    {
        $form = $this->formFactory->create(SignupType::class);

        $this->assertTrue($form->has('email'));
        $this->assertTrue($form->has('plainPassword'));
        $this->assertTrue($form->has('agreeTerms'));
        $this->assertTrue($form->has('submit'));
    }

    public function testEmailFieldIsEmailType(): void
    {
        $form = $this->formFactory->create(SignupType::class);

        $this->assertInstanceOf(
            EmailType::class,
            $form->get('email')->getConfig()->getType()->getInnerType()
        );
    }

    public function testPlainPasswordFieldIsRepeatedType(): void
    {
        $form = $this->formFactory->create(SignupType::class);

        $this->assertInstanceOf(
            RepeatedType::class,
            $form->get('plainPassword')->getConfig()->getType()->getInnerType()
        );
    }

    public function testAgreeTermsIsCheckboxType(): void
    {
        $form = $this->formFactory->create(SignupType::class);

        $this->assertInstanceOf(
            CheckboxType::class,
            $form->get('agreeTerms')->getConfig()->getType()->getInnerType()
        );
    }

    public function testAgreeTermsIsNotMapped(): void
    {
        $form = $this->formFactory->create(SignupType::class);

        $this->assertFalse($form->get('agreeTerms')->getConfig()->getMapped());
    }

    public function testLabelsCanBeOverridden(): void
    {
        $form = $this->formFactory->create(SignupType::class, null, [
            'label_email' => 'E-Mail',
            'label_password' => 'Passwort',
            'label_password_confirm' => 'Passwort bestätigen',
            'label_agree_terms' => 'AGB akzeptieren',
            'label_submit' => 'Registrieren',
        ]);

        $this->assertSame('E-Mail', $form->get('email')->getConfig()->getOption('label'));
        $this->assertSame('Registrieren', $form->get('submit')->getConfig()->getOption('label'));
        $this->assertSame('AGB akzeptieren', $form->get('agreeTerms')->getConfig()->getOption('label'));
    }

    public function testEmailHasAutocompleteAttribute(): void
    {
        $form = $this->formFactory->create(SignupType::class);

        $attr = $form->get('email')->getConfig()->getOption('attr');
        $this->assertSame('email', $attr['autocomplete']);
    }

    public function testTermsUrlPassedToAgreeTermsView(): void
    {
        $form = $this->formFactory->create(SignupType::class, null, [
            'terms_url' => '/terms',
        ]);

        $view = $form->createView();

        $this->assertSame('/terms', $view['agreeTerms']->vars['terms_url']);
    }
}
