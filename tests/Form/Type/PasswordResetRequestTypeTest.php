<?php

namespace Reactolith\SymfonyBundle\Tests\Form\Type;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\Form\Type\CardFormType;
use Reactolith\SymfonyBundle\Form\Type\PasswordResetRequestType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Forms;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordResetRequestTypeTest extends TestCase
{
    private PasswordResetRequestType $type;

    protected function setUp(): void
    {
        $this->type = new PasswordResetRequestType();
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertSame('reactolith_password_reset_request', $this->type->getBlockPrefix());
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
        $this->assertSame('Reset password', $resolved['card_title']);
        $this->assertSame('Enter your email address and we will send you a reset link', $resolved['card_description']);
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
        $this->assertSame('Remember your password?', $resolved['card_footer_text']);
        $this->assertSame('Back to login', $resolved['card_footer_link_label']);
        $this->assertSame('/login', $resolved['card_footer_link_url']);
    }

    public function testBuildFormAddsEmailAndSubmit(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new PasswordResetRequestType())
            ->getFormFactory();

        $form = $formFactory->create(PasswordResetRequestType::class);

        $this->assertTrue($form->has('email'));
        $this->assertTrue($form->has('submit'));
        $this->assertCount(2, $form);
    }

    public function testEmailFieldIsEmailType(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new PasswordResetRequestType())
            ->getFormFactory();

        $form = $formFactory->create(PasswordResetRequestType::class);

        $this->assertInstanceOf(
            EmailType::class,
            $form->get('email')->getConfig()->getType()->getInnerType()
        );
    }
}
