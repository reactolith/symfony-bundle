<?php

namespace Reactolith\SymfonyBundle\Tests\Form\Type;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\Form\Type\SeparatorType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeparatorTypeTest extends TestCase
{
    private SeparatorType $type;

    protected function setUp(): void
    {
        $this->type = new SeparatorType();
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertSame('reactolith_separator', $this->type->getBlockPrefix());
    }

    public function testDefaultOptions(): void
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(['mapped', 'label']);
        $resolver->setDefaults(['mapped' => true, 'label' => null]);

        $this->type->configureOptions($resolver);

        $resolved = $resolver->resolve([]);
        $this->assertFalse($resolved['mapped']);
        $this->assertFalse($resolved['label']);
    }

    public function testLabelCanBeSet(): void
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(['mapped', 'label']);
        $resolver->setDefaults(['mapped' => true, 'label' => null]);

        $this->type->configureOptions($resolver);

        $resolved = $resolver->resolve(['label' => 'Or continue with']);
        $this->assertSame('Or continue with', $resolved['label']);
    }
}
