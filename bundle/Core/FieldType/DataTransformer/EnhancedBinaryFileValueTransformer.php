<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Core\FieldType\DataTransformer;

use Ibexa\ContentForms\FieldType\DataTransformer\AbstractBinaryBaseTransformer;
use Ibexa\Contracts\Core\Repository\FieldType;
use Ibexa\Core\FieldType\Value;
use Symfony\Component\Form\DataTransformerInterface;

class EnhancedBinaryFileValueTransformer extends AbstractBinaryBaseTransformer implements DataTransformerInterface
{
    /**
     * @param FieldType $fieldType
     * @param Value|null $initialValue
     * @param string $valueClass
     */
    public function __construct(FieldType $fieldType, Value $initialValue, $valueClass)
    {
        parent::__construct($fieldType, $initialValue, $valueClass);
    }

    public function transform(mixed $value): array
    {
        if (null === $value) {
            $value = $this->fieldType->getEmptyValue();
        }

        return array_merge(
            $this->getDefaultProperties(),
            ['downloadCount' => $value->downloadCount]
        );
    }

    public function reverseTransform(mixed $value): ?Value
    {
        if (null === $value['file']) {
            return $this->fieldType->getEmptyValue();
        }

        /** @var \Ibexa\Core\FieldType\BinaryFile\Value */
        return $this->getReverseTransformedValue($value);
    }
}
