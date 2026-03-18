<?php

namespace Reactolith\SymfonyBundle\Tests\Form\Type;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\Form\Type\CardFormType;
use Reactolith\SymfonyBundle\Form\Type\PasswordResetConfirmType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Forms;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordResetConfirmTypeTest extends TestCase
{
    private PasswordResetConfirmType $type;

    protected function setUp(): void
    {
        $this->type = new PasswordResetConfirmType();
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertSame('reactolith_password_reset_confirm', $this->type->getBlockPrefix());
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
            'social_providers', 'csrf_protection',
        ]);

        $this->type->configureOptions($resolver);

        $resolved = $resolver->resolve([]);
        $this->assertSame('Set new password', $resolved['card_title']);
        $this->assertSame('Enter your new password below', $resolved['card_description']);
    }

    public function testBuildFormAddsPlainPasswordAndSubmit(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new PasswordResetConfirmType())
            ->getFormFactory();

        $form = $formFactory->create(PasswordResetConfirmType::class);

        $this->assertTrue($form->has('plainPassword'));
        $this->assertTrue($form->has('submit'));
    }

    public function testPlainPasswordIsRepeatedType(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new PasswordResetConfirmType())
            ->getFormFactory();

        $form = $formFactory->create(PasswordResetConfirmType::class);

        $this->assertInstanceOf(
            RepeatedType::class,
            $form->get('plainPassword')->getConfig()->getType()->getInnerType()
        );
    }

    public function testPlainPasswordHasNewPasswordAutocomplete(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new PasswordResetConfirmType())
            ->getFormFactory();

        $form = $formFactory->create(PasswordResetConfirmType::class);

        $options = $form->get('plainPassword')->getConfig()->getOptions();
        $this->assertSame('new-password', $options['options']['attr']['autocomplete']);
    }
}
