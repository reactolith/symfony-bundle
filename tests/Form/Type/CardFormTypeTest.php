<?php

namespace Reactolith\SymfonyBundle\Tests\Form\Type;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\Form\Type\CardFormType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CardFormTypeTest extends TestCase
{
    private CardFormType $type;

    protected function setUp(): void
    {
        $this->type = new CardFormType();
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertSame('reactolith_card', $this->type->getBlockPrefix());
    }

    public function testGetParentReturnsFormType(): void
    {
        $this->assertSame(FormType::class, $this->type->getParent());
    }

    public function testDefaultOptions(): void
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined([
            'card_title', 'card_description',
            'card_footer_text', 'card_footer_link_label', 'card_footer_link_url',
            'social_providers',
        ]);

        $this->type->configureOptions($resolver);

        $resolved = $resolver->resolve([]);
        $this->assertSame('', $resolved['card_title']);
        $this->assertSame('', $resolved['card_description']);
        $this->assertNull($resolved['card_footer_text']);
        $this->assertNull($resolved['card_footer_link_label']);
        $this->assertNull($resolved['card_footer_link_url']);
        $this->assertSame([], $resolved['social_providers']);
    }

    public function testCardTitleCanBeCustomized(): void
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(['card_title', 'card_description', 'card_footer_text', 'card_footer_link_label', 'card_footer_link_url', 'social_providers']);

        $this->type->configureOptions($resolver);

        $resolved = $resolver->resolve(['card_title' => 'My Form']);
        $this->assertSame('My Form', $resolved['card_title']);
    }

    public function testSocialProvidersCanBeSet(): void
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(['card_title', 'card_description', 'card_footer_text', 'card_footer_link_label', 'card_footer_link_url', 'social_providers']);

        $this->type->configureOptions($resolver);

        $providers = [
            ['name' => 'Google', 'url' => '/connect/google'],
            ['name' => 'GitHub', 'url' => '/connect/github'],
        ];
        $resolved = $resolver->resolve(['social_providers' => $providers]);
        $this->assertCount(2, $resolved['social_providers']);
        $this->assertSame('Google', $resolved['social_providers'][0]['name']);
    }
}
