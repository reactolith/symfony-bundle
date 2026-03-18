<?php

namespace Reactolith\SymfonyBundle\Tests\Form\Type;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\Form\Type\FieldGroupType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Forms;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FieldGroupTypeTest extends TestCase
{
    private FieldGroupType $type;

    protected function setUp(): void
    {
        $this->type = new FieldGroupType();
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertSame('reactolith_field_group', $this->type->getBlockPrefix());
    }

    public function testDefaultOptionsSetInheritData(): void
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(['inherit_data', 'row', 'gap']);
        $resolver->setDefaults(['inherit_data' => false, 'row' => false, 'gap' => '6']);

        $this->type->configureOptions($resolver);

        $resolved = $resolver->resolve([]);
        $this->assertTrue($resolved['inherit_data']);
        $this->assertFalse($resolved['row']);
        $this->assertSame('4', $resolved['gap']);
    }

    public function testRowOptionCanBeEnabled(): void
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(['inherit_data', 'row', 'gap']);
        $resolver->setDefaults(['inherit_data' => false, 'row' => false, 'gap' => '6']);

        $this->type->configureOptions($resolver);

        $resolved = $resolver->resolve(['row' => true]);
        $this->assertTrue($resolved['row']);
    }

    public function testGapOptionCanBeCustomized(): void
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(['inherit_data', 'row', 'gap']);
        $resolver->setDefaults(['inherit_data' => false, 'row' => false, 'gap' => '6']);

        $this->type->configureOptions($resolver);

        $resolved = $resolver->resolve(['gap' => '2']);
        $this->assertSame('2', $resolved['gap']);
    }

    public function testFieldGroupCanContainChildren(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addType(new FieldGroupType())
            ->getFormFactory();

        $form = $formFactory->createBuilder()
            ->add('name_group', FieldGroupType::class, ['row' => true])
            ->getForm();

        $form->get('name_group')
            ->getConfig()
            ->getFormFactory();

        // FieldGroupType with inherit_data creates a compound form
        $this->assertTrue($form->get('name_group')->getConfig()->getOption('inherit_data'));
    }
}
