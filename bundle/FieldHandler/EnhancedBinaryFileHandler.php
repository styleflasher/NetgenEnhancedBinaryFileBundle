<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\FieldHandler;

use Ibexa\Core\IO\Values\BinaryFile;
use DOMDocument;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\FieldType\Value;
use Ibexa\Core\IO\IOServiceInterface;
use Netgen\Bundle\EnhancedBinaryFileBundle\Core\FieldType\EnhancedBinaryFile\Value as EnhancedBinaryFileValue;
use Netgen\InformationCollection\API\FieldHandler\CustomLegacyFieldHandlerInterface;
use Netgen\InformationCollection\API\Value\Legacy\FieldValue as LegacyData;

class EnhancedBinaryFileHandler implements CustomLegacyFieldHandlerInterface
{
    /**
     * @var IOServiceInterface
     */
    protected $ioService;

    /**
     * EnhancedBinaryFileHandler constructor.
     *
     * @param IOServiceInterface $IOService
     */
    public function __construct(IOServiceInterface $ioService)
    {
        $this->ioService = $ioService;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Value $value): bool
    {
        return $value instanceof EnhancedBinaryFileValue;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(Value $value, FieldDefinition $fieldDefinition): string
    {
        return (string) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getLegacyValue(Value $value, FieldDefinition $fieldDefinition): LegacyData
    {
        return new LegacyData(
            $fieldDefinition->id,
            $this->store($value, $fieldDefinition)
        );
    }

    /**
     * Create XML doc string
     * and save file to filesystem.
     *
     * @param EnhancedBinaryFileValue $value
     * @param FieldDefinition $fieldDefinition
     *
     * @return string
     */
    protected function store(EnhancedBinaryFileValue $value, FieldDefinition $fieldDefinition)
    {
        $binaryFile = $this->storeBinaryFileToPath($value);

        $doc = new DOMDocument('1.0', 'utf-8');
        $root = $doc->createElement('binaryfile-info');
        $binaryFileList = $doc->createElement('binaryfile-attributes');

        $fileInfo = [
            'Filename' => htmlentities($binaryFile->uri),
            'OriginalFilename' => htmlentities($value->fileName),
            'Size' => $value->fileSize,
        ];

        foreach ($fileInfo as $key => $binaryFileItem) {
            $cdataElement = $doc->createCDATASection($binaryFileItem);
            $binaryFileElement = $doc->createElement($key);
            $binaryFileElement->appendChild($cdataElement);
            $binaryFileList->appendChild($binaryFileElement);
        }

        $root->appendChild($binaryFileList);
        $doc->appendChild($root);

        return $doc->saveXML();
    }

    /**
     * Stores file to filesystem.
     *
     * @param EnhancedBinaryFileValue $value
     * @param string $storagePrefix
     *
     * @return BinaryFile
     */
    protected function storeBinaryFileToPath(EnhancedBinaryFileValue $value, $storagePrefix = '/original/collected/')
    {
        $binaryCreateStruct = $this->ioService
            ->newBinaryCreateStructFromLocalFile($value->inputUri);
        $encodedFilename = uniqid();
        $binaryCreateStruct->id = $storagePrefix . $encodedFilename;

        $this->ioService->setPrefix(null); // do not put the file into 'images' subfolder.

        $binaryFile = $this->ioService->createBinaryFile($binaryCreateStruct);

        return $binaryFile;
    }
}
