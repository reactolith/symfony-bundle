<?php

namespace Reactolith\SymfonyBundle\Tests\Form\Type;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\Form\Type\SwitchType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SwitchTypeTest extends TestCase
{
    private SwitchType $type;

    protected function setUp(): void
    {
        $this->type = new SwitchType();
    }

    public function testGetParentReturnsCheckboxType(): void
    {
        $this->assertSame(CheckboxType::class, $this->type->getParent());
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertSame('reactolith_switch', $this->type->getBlockPrefix());
    }

    public function testConfigureOptionsSetsLabelAttr(): void
    {
        $resolver = new OptionsResolver();

        // CheckboxType normally sets its own defaults, but we only care about
        // what SwitchType overrides – so define the options it expects
        $resolver->setDefined(['label_attr']);
        $resolver->setDefault('label_attr', []);

        $this->type->configureOptions($resolver);

        $resolved = $resolver->resolve([]);
        $this->assertSame(['class' => ''], $resolved['label_attr']);
    }

    public function testConfigureOptionsCanBeOverridden(): void
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(['label_attr']);
        $resolver->setDefault('label_attr', []);

        $this->type->configureOptions($resolver);

        $resolved = $resolver->resolve(['label_attr' => ['class' => 'custom']]);
        $this->assertSame(['class' => 'custom'], $resolved['label_attr']);
    }
}
