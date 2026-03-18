<?php

namespace Reactolith\SymfonyBundle\Tests\Form\Type;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\Form\Type\AccountActivationType;
use Reactolith\SymfonyBundle\Form\Type\CardFormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Forms;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountActivationTypeTest extends TestCase
{
    private AccountActivationType $type;

    protected function setUp(): void
    {
        $this->type = new AccountActivationType();
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertSame('reactolith_account_activation', $this->type->getBlockPrefix());
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
            'social_providers', 'login_url', 'csrf_protection',
        ]);

        $this->type->configureOptions($resolver);

        $resolved = $resolver->resolve([]);
        $this->assertSame('Activate your account', $resolved['card_title']);
        $this->assertSame('Click the button below to activate your account', $resolved['card_description']);
    }

    public function testLoginUrlSetsCardFooter(): void
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined([
            'card_title', 'card_description', 'card_footer_text',
            'card_footer_link_label', 'card_footer_link_url',
            'social_providers', 'login_url', 'csrf_protection',
        ]);

        $this->type->configureOptions($resolver);

        $resolved = $resolver->resolve(['login_url' => '/login']);
        $this->assertSame('Already activated?', $resolved['card_footer_text']);
        $this->assertSame('Login', $resolved['card_footer_link_label']);
        $this->assertSame('/login', $resolved['card_footer_link_url']);
    }

    public function testBuildFormAddsOnlySubmit(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new AccountActivationType())
            ->getFormFactory();

        $form = $formFactory->create(AccountActivationType::class);

        $this->assertTrue($form->has('submit'));
        $this->assertCount(1, $form);
    }

    public function testSubmitFieldIsSubmitType(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new AccountActivationType())
            ->getFormFactory();

        $form = $formFactory->create(AccountActivationType::class);

        $this->assertInstanceOf(
            SubmitType::class,
            $form->get('submit')->getConfig()->getType()->getInnerType()
        );
    }
}
