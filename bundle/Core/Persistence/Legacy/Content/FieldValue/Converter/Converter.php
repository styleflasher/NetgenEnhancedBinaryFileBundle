<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Core\Persistence\Legacy\Content\FieldValue\Converter;

use Ibexa\Core\FieldType\FieldSettings;
use Ibexa\Core\Persistence\Legacy\Content\FieldValue\Converter as ConverterInterface;
use Ibexa\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use Ibexa\Core\Persistence\Legacy\Content\StorageFieldValue;
use Ibexa\Contracts\Core\Persistence\Content\FieldTypeConstraints;
use Ibexa\Contracts\Core\Persistence\Content\FieldValue;
use Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition;

class Converter implements ConverterInterface
{
    /**
     * Converts data from $value to $storageFieldValue.
     *
     * @param \Ibexa\Contracts\Core\Persistence\Content\FieldValue $value
     * @param \Ibexa\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue)
    {
    }

    /**
     * Converts data from $value to $fieldValue.
     *
     * @param \Ibexa\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \Ibexa\Contracts\Core\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue)
    {
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef.
     *
     * @param \Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \Ibexa\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef)
    {
        $storageDef->dataInt1 = isset($fieldDef->fieldTypeConstraints->validators['FileSizeValidator']['maxFileSize']) ?
            $fieldDef->fieldTypeConstraints->validators['FileSizeValidator']['maxFileSize'] :
            0;
        $storageDef->dataText1 = isset($fieldDef->fieldTypeConstraints->fieldSettings['allowedTypes']) ?
            $fieldDef->fieldTypeConstraints->fieldSettings['allowedTypes'] :
            '';
        $storageDef->dataText2 = isset($fieldDef->fieldTypeConstraints->fieldSettings['mimeTypesMessage']) ?
            $fieldDef->fieldTypeConstraints->fieldSettings['mimeTypesMessage'] :
            '';
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef.
     *
     * @param \Ibexa\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef)
    {
        $fieldDef->fieldTypeConstraints = new FieldTypeConstraints(
            [
                'validators' => [
                    'FileSizeValidator' => [
                        'maxFileSize' => (0 !== $storageDef->dataInt1
                            ? $storageDef->dataInt1
                            : null),
                    ],
                ],
                'fieldSettings' => new FieldSettings([
                    'allowedTypes' => $storageDef->dataText1,
                    'mimeTypesMessage' => $storageDef->dataText2,
                ]),
            ]
        );
    }

    /**
     * Returns the name of the index column in the attribute table.
     *
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     *
     * If the indexing is not supported, this method must return false.
     *
     * @return string|false
     */
    public function getIndexColumn()
    {
        return false;
    }
}
