<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Core\FieldType\EnhancedBinaryFile;

use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Netgen\Bundle\EnhancedBinaryFileBundle\Core\FieldType\DataTransformer\EnhancedBinaryFileValueTransformer;
use Netgen\Bundle\EnhancedBinaryFileBundle\Core\FieldType\EnhancedBinaryFile\Value as FieldValue;
use Netgen\Bundle\EnhancedBinaryFileBundle\Form\Type\EnhancedBinaryFileFieldType;
use Symfony\Component\Form\FormInterface;

class FieldValueFormMapper implements FieldValueFormMapperInterface
{
    public function __construct(
        private readonly FieldTypeService        $fieldTypeService,
        private readonly ConfigResolverInterface $configResolver
    )
    {
    }

    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data): void
    {
        $fieldDefinition = $data->getFieldDefinition();
        $formConfig = $fieldForm->getConfig();
        $fieldType = $this->fieldTypeService->getFieldType($fieldDefinition->getFieldTypeIdentifier());
        $allowedFileExtensions = $fieldDefinition->getFieldSettings()['allowedTypes'] ?? [];
        $mimeTypesMessage = $fieldDefinition->getFieldSettings()['mimeTypesMessage'] ?? null;

        $allowedMimeTypes = [];
        if (!empty($allowedFileExtensions)) {
            $allowedExtensions = explode('|', $allowedFileExtensions);

            foreach ($allowedExtensions as $allowedExtension) {
                if ($this->configResolver->hasParameter("{$allowedExtension}.Types", 'mime')) {
                    $allowedMimeTypes = array_merge($allowedMimeTypes, $this->configResolver->getParameter("{$allowedExtension}.Types", 'mime'));
                }
            }
        }

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        EnhancedBinaryFileFieldType::class,
                        [
                            'required' => $fieldDefinition->isRequired(),
                            'label' => $fieldDefinition->getName(),
                            'mime_types' => array_unique($allowedMimeTypes),
                            'mime_types_message' => $mimeTypesMessage,
                        ]
                    )
                    ->addModelTransformer(new EnhancedBinaryFileValueTransformer($fieldType, $data->getField()->getValue(), FieldValue::class))
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
}
