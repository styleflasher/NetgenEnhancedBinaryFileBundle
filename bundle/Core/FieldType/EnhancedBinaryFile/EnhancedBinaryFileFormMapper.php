<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Core\FieldType\EnhancedBinaryFile;

use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\AdminUi\FieldType\Mapper\BinaryFileFormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;

class EnhancedBinaryFileFormMapper extends BinaryFileFormMapper
{
    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data): void
    {
        parent::mapFieldDefinitionForm($fieldDefinitionForm, $data);

        $fieldDefinitionForm
            ->add(
                'allowedTypes',
                TextType::class,
                [
                    'required' => false,
                    'property_path' => 'fieldSettings[allowedTypes]',
                    'label' => 'field_definition.enhancedbinaryfile.allowedTypes',
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'mimeTypesMessage',
                TextType::class,
                [
                    'required' => false,
                    'property_path' => 'fieldSettings[mimeTypesMessage]',
                    'label' => 'field_definition.enhancedbinaryfile.mimeTypesMessage',
                    'translation_domain' => 'messages',
                ]
            );
    }
}
