<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Tests\Core\FieldType\Persistence\Legacy\Content\FieldValue\Converter;

use Ibexa\Contracts\Core\Persistence\Content\FieldTypeConstraints;
use Ibexa\Contracts\Core\Persistence\Content\FieldValue;
use Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use Ibexa\Core\Persistence\Legacy\Content\StorageFieldValue;
use Netgen\Bundle\EnhancedBinaryFileBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\Converter;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    protected $converter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configResolver;

    public function setUp(): void
    {
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->converter = new Converter();
    }

    public function testInstanceOfConverterInterface()
    {
        $this->assertInstanceOf(\Ibexa\Core\Persistence\Legacy\Content\FieldValue\Converter::class, $this->converter);
    }

    public function testToStorageValue()
    {
        $fieldValue = $this->createMock(FieldValue::class);
        $fieldValue->data = 'some value';
        $storage = new StorageFieldValue();

        $this->converter->toStorageValue($fieldValue, $storage);
        $this->assertEquals('some value', $storage->dataText);
    }

    public function testToFieldValue()
    {
        $storage = new StorageFieldValue();
        $storage->dataText = 'some value';
        $fieldValue = $this->createMock(FieldValue::class);

        $this->converter->toFieldValue($storage, $fieldValue);
        $this->assertEquals('some value', $fieldValue->data);
    }

    public function testGetIndexColumnShouldReturnFalse()
    {
        $this->assertFalse($this->converter->getIndexColumn());
    }

    public function testToStorageFieldDefinitionWithoutConstraints()
    {
        $fieldDefinition = $this->createMock(FieldDefinition::class);
        $fieldDefinition->fieldTypeConstraints = new FieldTypeConstraints();
        $storage = new StorageFieldDefinition();
        $this->converter->toStorageFieldDefinition($fieldDefinition, $storage);

        $this->assertEquals(0, $storage->dataInt1);
        $this->assertEquals('', $storage->dataText1);
    }

    public function testToStorageFieldDefinition()
    {
        $fieldDefinition = $this->createMock(FieldDefinition::class);
        $fieldDefinition->fieldTypeConstraints = new FieldTypeConstraints();
        $fieldDefinition->fieldTypeConstraints->validators = [
            'FileSizeValidator' => [
                'maxFileSize' => 14,
            ],
        ];
        $fieldDefinition->fieldTypeConstraints->fieldSettings = [
            'allowedTypes' => [
                'text/plain',
            ],
        ];
        $storage = new StorageFieldDefinition();
        $this->converter->toStorageFieldDefinition($fieldDefinition, $storage);

        $this->assertEquals(14, $storage->dataInt1);
        $this->assertEquals(['text/plain'], $storage->dataText1);
    }

    public function testToFieldDefinition()
    {
        $fieldDefinition = $this->createMock(FieldDefinition::class);
        $storage = new StorageFieldDefinition();

        $this->converter->toFieldDefinition($storage, $fieldDefinition);

        $this->assertInstanceOf(FieldTypeConstraints::class, $fieldDefinition->fieldTypeConstraints);
        $this->assertNull($fieldDefinition->fieldTypeConstraints->validators['FileSizeValidator']['maxFileSize']);
        $this->assertEquals('', $fieldDefinition->fieldTypeConstraints->fieldSettings['allowedTypes']);
    }

    public function testToFieldDefinitionWithValidator()
    {
        $fieldDefinition = $this->createMock(FieldDefinition::class);
        $storage = new StorageFieldDefinition();
        $storage->dataInt1 = 55;
        $storage->dataText1 = 'text/plain';

        $this->converter->toFieldDefinition($storage, $fieldDefinition);

        $this->assertInstanceOf(FieldTypeConstraints::class, $fieldDefinition->fieldTypeConstraints);
        $this->assertEquals(55, $fieldDefinition->fieldTypeConstraints->validators['FileSizeValidator']['maxFileSize']);
        $this->assertEquals('text/plain', $fieldDefinition->fieldTypeConstraints->fieldSettings['allowedTypes']);
    }
}
