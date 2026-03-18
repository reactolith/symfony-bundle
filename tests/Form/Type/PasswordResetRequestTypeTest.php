<?php

namespace Reactolith\SymfonyBundle\Tests\Form\Type;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\Form\Type\CardFormType;
use Reactolith\SymfonyBundle\Form\Type\PasswordResetRequestType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Forms;

class PasswordResetRequestTypeTest extends TestCase
{
    private PasswordResetRequestType $type;
    private $formFactory;

    protected function setUp(): void
    {
        $this->type = new PasswordResetRequestType();
        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addType(new CardFormType())
            ->addType(new PasswordResetRequestType())
            ->getFormFactory();
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
        $form = $this->formFactory->create(PasswordResetRequestType::class);
        $options = $form->getConfig()->getOptions();

        $this->assertSame('Reset password', $options['card_title']);
        $this->assertSame('Enter your email address and we will send you a reset link', $options['card_description']);
    }

    public function testLoginUrlSetsFooterInView(): void
    {
        $form = $this->formFactory->create(PasswordResetRequestType::class, null, [
            'login_url' => '/login',
        ]);
        $view = $form->createView();

        $this->assertSame('Remember your password?', $view->vars['card_footer_text']);
        $this->assertSame('Back to login', $view->vars['card_footer_link_label']);
        $this->assertSame('/login', $view->vars['card_footer_link_url']);
    }

    public function testBuildFormAddsEmailAndSubmit(): void
    {
        $form = $this->formFactory->create(PasswordResetRequestType::class);

        $this->assertTrue($form->has('email'));
        $this->assertTrue($form->has('submit'));
        $this->assertCount(2, $form);
    }

    public function testEmailFieldIsEmailType(): void
    {
        $form = $this->formFactory->create(PasswordResetRequestType::class);

        $this->assertInstanceOf(
            EmailType::class,
            $form->get('email')->getConfig()->getType()->getInnerType()
        );
    }
}
