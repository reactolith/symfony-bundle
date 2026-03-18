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
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignupTypeTest extends TestCase
{
    private SignupType $type;

    protected function setUp(): void
    {
        $this->type = new SignupType();
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
        $resolver = new OptionsResolver();
        $resolver->setDefined([
            'card_title', 'card_description', 'card_footer_text',
            'card_footer_link_label', 'card_footer_link_url',
            'social_providers', 'login_url', 'terms_url',
            'csrf_protection',
        ]);

        $this->type->configureOptions($resolver);

        $resolved = $resolver->resolve([]);
        $this->assertSame('Create an account', $resolved['card_title']);
        $this->assertSame('Enter your details below to create your account', $resolved['card_description']);
        $this->assertNull($resolved['login_url']);
        $this->assertNull($resolved['terms_url']);
    }

    public function testLoginUrlSetsCardFooter(): void
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined([
            'card_title', 'card_description', 'card_footer_text',
            'card_footer_link_label', 'card_footer_link_url',
            'social_providers', 'login_url', 'terms_url',
            'csrf_protection',
        ]);

        $this->type->configureOptions($resolver);

        $resolved = $resolver->resolve(['login_url' => '/login']);
        $this->assertSame('Already have an account?', $resolved['card_footer_text']);
        $this->assertSame('Login', $resolved['card_footer_link_label']);
        $this->assertSame('/login', $resolved['card_footer_link_url']);
    }

    public function testBuildFormAddsExpectedFields(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new SignupType())
            ->getFormFactory();

        $form = $formFactory->create(SignupType::class);

        $this->assertTrue($form->has('email'));
        $this->assertTrue($form->has('plainPassword'));
        $this->assertTrue($form->has('agreeTerms'));
        $this->assertTrue($form->has('submit'));
    }

    public function testEmailFieldIsEmailType(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new SignupType())
            ->getFormFactory();

        $form = $formFactory->create(SignupType::class);

        $this->assertInstanceOf(
            EmailType::class,
            $form->get('email')->getConfig()->getType()->getInnerType()
        );
    }

    public function testPlainPasswordFieldIsRepeatedType(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new SignupType())
            ->getFormFactory();

        $form = $formFactory->create(SignupType::class);

        $this->assertInstanceOf(
            RepeatedType::class,
            $form->get('plainPassword')->getConfig()->getType()->getInnerType()
        );
    }

    public function testAgreeTermsIsCheckboxType(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new SignupType())
            ->getFormFactory();

        $form = $formFactory->create(SignupType::class);

        $this->assertInstanceOf(
            CheckboxType::class,
            $form->get('agreeTerms')->getConfig()->getType()->getInnerType()
        );
    }

    public function testAgreeTermsIsNotMapped(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new SignupType())
            ->getFormFactory();

        $form = $formFactory->create(SignupType::class);

        $this->assertFalse($form->get('agreeTerms')->getConfig()->getMapped());
    }

    public function testEmailHasAutocompleteAttribute(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new SignupType())
            ->getFormFactory();

        $form = $formFactory->create(SignupType::class);

        $attr = $form->get('email')->getConfig()->getOption('attr');
        $this->assertSame('email', $attr['autocomplete']);
    }

    public function testTermsUrlPassedToAgreeTermsView(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new SignupType())
            ->getFormFactory();

        $form = $formFactory->create(SignupType::class, null, [
            'terms_url' => '/terms',
        ]);

        $view = $form->createView();

        $this->assertSame('/terms', $view['agreeTerms']->vars['terms_url']);
    }
}
