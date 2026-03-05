<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Tests\FieldHandler;

use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\FieldType\Integer\Value as IntValue;
use Ibexa\Core\IO\IOServiceInterface;
use Ibexa\Core\IO\Values\BinaryFile;
use Ibexa\Core\IO\Values\BinaryFileCreateStruct;
use Netgen\Bundle\EnhancedBinaryFileBundle\Core\FieldType\EnhancedBinaryFile\Value;
use Netgen\Bundle\EnhancedBinaryFileBundle\FieldHandler\EnhancedBinaryFileHandler;
use Netgen\InformationCollection\API\FieldHandler\CustomLegacyFieldHandlerInterface;
use PHPUnit\Framework\TestCase;

class EnhancedBinaryFileHandlerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $io;

    /**
     * @var EnhancedBinaryFileHandler
     */
    protected $handler;

    public function setUp(): void
    {
        $this->io = $this->createMock(IOServiceInterface::class);
        $this->handler = new EnhancedBinaryFileHandler($this->io);
    }

    public function testInstanceOfCustomLegacyFieldHandlerInterface()
    {
        $this->assertInstanceOf(CustomLegacyFieldHandlerInterface::class, $this->handler);
    }

    public function testSupports()
    {
        $this->assertTrue($this->handler->supports(new Value()));
        $this->assertFalse($this->handler->supports(new IntValue(1)));
    }

    public function testToString()
    {
        $file = new Value(
            [
                'uri' => 'test.txt',
            ]
        );

        $fieldDefinition = $this->createMock(FieldDefinition::class);
        $this->assertEquals('test.txt', $this->handler->toString($file, $fieldDefinition));
    }

    public function testGetLegacyValue()
    {
        $file = new Value(
            [
                'inputUri' => '/tmp/test.txt',
                'fileName' => 'test.txt',
                'fileSize' => 123,
            ]
        );

        $struct = new BinaryFileCreateStruct();
        $binaryFile = new BinaryFile(['uri' => 'path/to/stored/file.txt']);

        $this->io->expects($this->once())
            ->method('newBinaryCreateStructFromLocalFile')
            ->with($file->inputUri)
            ->willReturn($struct);

        $this->io->expects($this->once())
            ->method('createBinaryFile')
            ->with($struct)
            ->willReturn($binaryFile);

        $fieldDefinition = $this->createMock(FieldDefinition::class);
        $fieldDefinition->method('getId')->willReturn(123);
        $data = $this->handler->getLegacyValue($file, $fieldDefinition);
        $this->assertStringContainsString('path/to/stored/file.txt', $data->getDataText());
        $this->assertStringContainsString('test.txt', $data->getDataText());
        $this->assertStringContainsString('123', $data->getDataText());
    }

    public function testGetLegacyValueEmpty()
    {
        $file = new Value();

        $this->io->expects($this->never())
            ->method('newBinaryCreateStructFromLocalFile');

        $fieldDefinition = $this->createMock(FieldDefinition::class);
        $fieldDefinition->method('getId')->willReturn(456);
        $data = $this->handler->getLegacyValue($file, $fieldDefinition);

        $this->assertEquals(456, $data->getFieldDefinitionId());
        $this->assertEquals('', $data->getDataText());
    }
}
